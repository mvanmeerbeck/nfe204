<?php

namespace Nfe204\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Nfe204\Classifier\Classifier;
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
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'category_id']
                        ]
                    ]
                ]
            ]
        ]);

        $classifier = new Classifier($this->client);

        foreach ($result['hits']['hits'] as $offer) {
            var_dump($classifier->predict($offer));
        }
    }
}
