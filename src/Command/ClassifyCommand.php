<?php

namespace Nfe204\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nfe204\Classifier\AbstractClassifier;
use Nfe204\Classifier\Classifier;
use Nfe204\Classifier\MoreLikeThisClassifier;
use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClassifyCommand extends Command
{
    /**
     * @var Client $client
     */
    protected $client;
    protected $config = array();
    /**
     * @var AbstractClassifier $classifier
     */
    protected $classifier;

    /**
     * @var ProgressBar $progressBar
     */
    protected $progressBar;

    public function configure()
    {
        $this
            ->setName('classify')
            ->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, null, 10)
            ->addOption('size', 's', InputOption::VALUE_OPTIONAL, null, 100)
            ->addOption('classifier', 'c', InputOption::VALUE_OPTIONAL, null, 'ft')
            ->addOption('log', 'l', InputOption::VALUE_OPTIONAL, null, 'var/logs');
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = ClientBuilder::create()
            ->setHosts($this->config['elasticsearch'])
            ->build();

        $this->progressBar = new ProgressBar($output);

        $this->progressBar->setRedrawFrequency(10);
        $this->progressBar->setOverwrite(true);
        $this->progressBar->setFormatDefinition('custom', ' %current%/%max% accuracy=%accuracy% precision=%precision% recall=%recall% f1score=%fscore%');
        $this->progressBar->setFormat('custom');
        $this->progressBar->setMessage(0, 'accuracy');
        $this->progressBar->setMessage(0, 'precision');
        $this->progressBar->setMessage(0, 'recall');
        $this->progressBar->setMessage(0, 'fscore');

        switch ($input->getOption('classifier')) {
            case 'ft':
                $this->classifier = new Classifier($this->client, $input->getOption('log'));
                break;
            case 'mlt':
                $this->classifier = new MoreLikeThisClassifier($this->client, $input->getOption('log'));
                break;
            default:
                throw new \Exception('Classifier not found');
                break;
        }

        $this->progressBar->start($input->getOption('batch') * $input->getOption('size'));

        $result = $this->client->search([
            'index' => 'offer',
            'size' => $input->getOption('size'),
            'scroll' => '1m',
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'category_id'],
                        ]
                    ]
                ],
            ]
        ]);

        for ($i = 0; $i < $input->getOption('batch'); $i++) {
            foreach ($result['hits']['hits'] as $offer) {
                $this->classifier->predict($offer['_source']);

                $this->progressBar->advance();
            }

            $report = new ClassificationReport($this->classifier->getActualLabels(), $this->classifier->getPredictedLabels());

            $average = $report->getAverage();

            $accuracy = Accuracy::score($this->classifier->getActualLabels(), $this->classifier->getPredictedLabels());

            $this->progressBar->setMessage(round($average['precision'], 4), 'precision');
            $this->progressBar->setMessage(round($average['recall'], 4), 'recall');
            $this->progressBar->setMessage(round($average['f1score'], 4), 'fscore');
            $this->progressBar->setMessage(round($accuracy, 4), 'accuracy');

            $result = $this->client->scroll([
                'scroll' => '1m',
                'scroll_id' => $result['_scroll_id']
            ]);
        }

        $this->progressBar->finish();
    }
}
