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

        $this->fetch();
    }

    private function fetch($cursor = '*', $rows = 1000)
    {
        /**
         * @var Query $query
         */
        $query = $this->client->createQuery(Client::QUERY_SELECT);

        $query
            ->setFields([
                'id', 'type',
                'offer_id', 'offer_brand_name', 'offer_brand_id', 'offer_name', 'offer_price', 'offer_price_regular', 'offer_category_name', 'description',
                'brand_id', 'brand_name',
                'shop_id', 'shop_name',
                'category_hierarchy_ids', 'category_hierarchy_names', 'category_id', 'category_name',
                'score_popularity'
            ])
            ->addFilterQuery([
                'key' => 'type',
                'query' => 'type:offer'
            ])
            ->setQuery('shop_id:25672')
            ->setRows($rows)
            ->addParam('cursorMark', $cursor)
            ->addSort('id', Query::SORT_ASC);

        $resultset = $this->client->execute($query);

        foreach ($resultset as $document) {
            echo json_encode($document->getFields()) . PHP_EOL;
        }

        if ($cursor !== $resultset->getData()['nextCursorMark']) {
            $this->fetch($resultset->getData()['nextCursorMark']);
        }
    }
}