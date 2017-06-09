<?php

namespace Nfe204\Command;

use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EsImportCommand extends Command
{
    protected $client;
    protected $config = array();

    public function configure()
    {
        $this
            ->setName('es:import')
            ->addArgument('type', InputArgument::REQUIRED)
            ->addArgument('file', InputArgument::REQUIRED);
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

        $handle = fopen($input->getArgument('file'), 'r');

        $i = 0;

        $documents = ['body' => []];
        while ($line = fgets($handle, 1024)) {
            $documents['body'][] = [
                'index' => [
                    '_index' => 'product',
                    '_type' => 'document',
                    '_id' => $i
                ]
            ];

            $documents['body'][] = json_decode($line, true);

            if (0 === $i % 1000) {
                $this->client->bulk($documents);

                $documents = ['body' => []];
            }

            $i++;
        }
    }
}
