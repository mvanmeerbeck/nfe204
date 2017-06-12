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
        while ($line = fgets($handle, 2048)) {
            $document = json_decode($line, true);

            $documents['body'][] = [
                'index' => [
                    '_index' => $input->getArgument('type'),
                    '_type' => 'document',
                    '_id' => $document[$input->getArgument('type') . '_id']
                ]
            ];

            $documents['body'][] = $document;

            $i++;

            if (0 === $i % 1000) {
                $this->bulk($documents);

                $documents = ['body' => []];
                $output->writeln($i . ' documents inserted');
            }
        }

        $this->bulk($documents);
        $output->writeln($i . ' documents inserted');
    }

    private function bulk(array $documents)
    {
        $result = $this->client->bulk($documents);

        if ($result['errors']) {
            foreach ($result['items'] as $item) {
                if (isset($item['index']['error'])) {
                    throw new \Exception(sprintf('Document %d failed to be inserted: %s', $item['index']['_id'], $item['index']['error']['reason']));
                }
            }
        }
    }
}
