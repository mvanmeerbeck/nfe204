<?php

namespace Nfe204\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nfe204\Classifier\AbstractClassifier;
use Nfe204\Classifier\Classifier;
use Phpml\Metric\ClassificationReport;
use Symfony\Component\Console\Command\Command;
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
            'size' => 10,
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

        $this->classifier = new Classifier($this->client);

        $this->scroll($output, $result);
    }

    private function scroll(OutputInterface $output, $result)
    {
        foreach ($result['hits']['hits'] as $i => $offer) {
            $this->classifier->predict($offer['_source']);
        }

        $report = new ClassificationReport($this->classifier->getActualLabels(), $this->classifier->getPredictedLabels());

        print_r($report->getAverage());

        if (isset($result['_scroll_id'])) {
            $result = $this->client->scroll([
                'scroll' => '1m',
                'scroll_id' => $result['_scroll_id']
            ]);

            $this->scroll($output, $result);
        }
    }
}
