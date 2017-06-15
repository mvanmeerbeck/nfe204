<?php

namespace Nfe204\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nfe204\Classifier\AbstractClassifier;
use Nfe204\Classifier\Classifier;
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

    const SIZE = 10;

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

        $this->progressBar = new ProgressBar($output);

        $this->progressBar->setRedrawFrequency(10);
        $this->progressBar->setOverwrite(true);
        $this->progressBar->setFormatDefinition('custom', ' %current%/%max% -- precision=%precision% recall=%recall% f1score=%fscore%');
        $this->progressBar->setFormat('custom');
        $this->progressBar->setMessage(0, 'precision');
        $this->progressBar->setMessage(0, 'recall');
        $this->progressBar->setMessage(0, 'fscore');

        $this->progressBar->start($result['hits']['total']);

        $this->classifier = new Classifier($this->client);

        $this->scroll($output, $result);

        $this->progressBar->finish();
    }

    private function scroll(OutputInterface $output, $result, $count = 0)
    {
        foreach ($result['hits']['hits'] as $i => $offer) {
            $this->classifier->predict($offer['_source']);

            $report = new ClassificationReport($this->classifier->getActualLabels(), $this->classifier->getPredictedLabels());

            $average = $report->getAverage();

            $this->progressBar->setMessage(round($average['precision'], 2), 'precision');
            $this->progressBar->setMessage(round($average['recall'], 2), 'recall');
            $this->progressBar->setMessage(round($average['f1score'], 2), 'fscore');

            $this->progressBar->advance();
        }

        if (isset($result['_scroll_id'])) {
            $result = $this->client->scroll([
                'scroll' => '1m',
                'scroll_id' => $result['_scroll_id']
            ]);

            $this->scroll($output, $result, $count + count($result['hits']));
        }
    }
}
