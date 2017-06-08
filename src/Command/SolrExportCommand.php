<?php

namespace Nfe204\Command;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{
    protected $client;
    protected $config = array();

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
        $this->client = new \Solarium\Client($this->config['solarium']);

        $cursor = '*';
        $nextCursor = null;
        $i = 0;
        $rows = 1000;

        do {
            $nextCursor = $this->fetch($cursor, $rows);

            $i += $rows;
            echo $i . PHP_EOL;
            sleep(1);
        } while ($nextCursor != $cursor && ($cursor = $nextCursor) && $i < 8000);
    }

    private function fetch($cursor = '*', $rows)
    {
        /**
         * @var Query $query
         */
        $query = $this->client->createQuery(Client::QUERY_SELECT);

        $query
            ->setQuery('shop_id:25672')
            ->setRows($rows)
            ->addParam('cursorMark', $cursor)
            ->addSort('id', Query::SORT_ASC);

        $resultset = $this->client->execute($query);

        foreach ($resultset as $document) {
            echo json_encode($document->getFields()) . PHP_EOL;
        }

        return $resultset->getData()['nextCursorMark'];
    }
}