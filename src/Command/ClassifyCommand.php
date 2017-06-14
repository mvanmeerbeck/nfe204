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
            'size' => 1000,
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

        $this->scroll($output, $result, [
            'positive' => 0,
            'total' => 0,
        ]);
    }

    private function scroll(OutputInterface $output, $result, array $predictions)
    {
        foreach ($result['hits']['hits'] as $i => $offer) {
            if (true === $this->classifier->predict($offer['_source'])) {
                $predictions['positive']++;
            }

            $predictions['total']++;
        }

        $output->writeln(round($predictions['positive'] / $predictions['total'] * 100) . '%');

        if (isset($result['_scroll_id'])) {
            $result = $this->client->scroll([
                'scroll' => '1m',
                'scroll_id' => $result['_scroll_id']
            ]);

            $this->scroll($output, $result, $predictions);
        }
    }
}
