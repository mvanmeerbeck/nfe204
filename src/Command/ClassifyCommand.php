<?php

namespace Nfe204\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nfe204\Classifier\AbstractClassifier;
use Nfe204\Classifier\Classifier;
use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
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

    const SIZE = 100;

    public function configure()
    {
        $this
            ->setName('classify');
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

        $this->classifier = new Classifier($this->client, 'var/logs');

        $this->scroll($output);

        $this->progressBar->finish();
    }

    private function scroll(OutputInterface $output, $scrollId = null)
    {
        if (is_null($scrollId)) {
            $result = $this->client->search([
                'index' => 'offer',
                'size' => self::SIZE,
                'scroll' => '1m',
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => [
                                'exists' => ['field' => 'category_id']
                            ]
                        ]
                    ],
                ]
            ]);

            $this->progressBar->start($result['hits']['total']);
        } else {
            $result = $this->client->scroll([
                'scroll' => '1m',
                'scroll_id' => $scrollId
            ]);
        }

        foreach ($result['hits']['hits'] as $i => $offer) {
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

        if (isset($result['_scroll_id'])) {
            $this->scroll($output, $result['_scroll_id']);
        }
    }
}
