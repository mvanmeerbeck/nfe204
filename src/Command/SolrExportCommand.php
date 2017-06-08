<?php

namespace Nfe204\Command;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{
    public function configure()
    {
        $this->setName('nfe204:solr:export');
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new \Solarium\Client($this->config['solarium']);

        $query = $client->createQuery($client::QUERY_SELECT);
        $query
            ->addParam('cursorMark', 'AoEub2ZmZXIxMDAwMTIyMTM=')
            ->addSort('id', Query::SORT_ASC)
        ;

        $resultset = $client->execute($query);
        print_r($resultset->getData()['nextCursorMark']);
        foreach ($resultset as $document) {
            echo json_encode($document->getFields()) . PHP_EOL;
        }
    }
}