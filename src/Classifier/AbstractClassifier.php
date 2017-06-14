<?php

namespace Nfe204\Classifier;

use Elasticsearch\Client;

abstract class AbstractClassifier
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}