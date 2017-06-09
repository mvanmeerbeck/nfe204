<?php

namespace Nfe204\Command;

use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{
    protected $client;
    protected $config = array();

    CONST TYPE_OFFER = 'offer';
    CONST TYPE_PRODUCT = 'product';

    public function configure()
    {
        $this
            ->setName('solr:export')
            ->addArgument('type', InputArgument::REQUIRED);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = new \Solarium\Client($this->config['solarium']);

        $this->fetch($input->getArgument('type'));
    }

    private function fetch($type, $cursor = '*', $rows = 100000)
    {
        /**
         * @var Query $query
         */
        $query = $this->client->createQuery(Client::QUERY_SELECT);

        if (self::TYPE_OFFER === $type) {
            $query
                ->setFields([
                    'id',
                    'offer_id', 'offer_brand_name', 'offer_brand_id', 'offer_name', 'offer_price', 'offer_price_regular', 'offer_category_name', 'offer_url', 'description',
                    'brand_id', 'brand_name',
                    'shop_id', 'shop_name',
                    'category_hierarchy_ids', 'category_hierarchy_names', 'category_id', 'category_name',
                    'score_popularity'
                ])
                ->addFilterQuery([
                    'key' => 'type',
                    'query' => 'type:' . self::TYPE_OFFER
                ])
                ->setRows($rows)
                ->addParam('cursorMark', $cursor)
                ->addSort('id', Query::SORT_ASC);
        }

        if (self::TYPE_PRODUCT === $type) {
            $query
                ->setFields([
                    'id',
                    'product_id', 'product_name', 'product_price_min', 'product_price_max',
                    'brand_id', 'brand_name',
                    'category_hierarchy_ids', 'category_hierarchy_names', 'category_id', 'category_name',
                    'score_popularity'
                ])
                ->addFilterQuery([
                    'key' => 'type',
                    'query' => 'type:' . self::TYPE_PRODUCT
                ])
                ->setRows($rows)
                ->addParam('cursorMark', $cursor)
                ->addSort('id', Query::SORT_ASC);
        }

        $resultset = $this->client->execute($query);
        $nextCursor = $resultset->getData()['nextCursorMark'];

        foreach ($resultset as $document) {
            echo json_encode($document->getFields()) . PHP_EOL;
        }

        unset($resultset);
        unset($query);

        if ($cursor !== $nextCursor) {
            $this->fetch($type, $nextCursor);
        }
    }
}
