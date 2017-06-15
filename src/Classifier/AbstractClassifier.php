<?php

namespace Nfe204\Classifier;

use Elasticsearch\Client;

abstract class AbstractClassifier
{
    protected $client;
    protected $actualLabels = [];
    protected $predictedLabels = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getActualLabels()
    {
        return $this->actualLabels;
    }

    public function getPredictedLabels()
    {
        return $this->predictedLabels;
    }
}