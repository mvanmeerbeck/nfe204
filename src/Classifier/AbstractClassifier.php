<?php

namespace Nfe204\Classifier;

use Elasticsearch\Client;
use Nfe204\Evaluation;

abstract class AbstractClassifier
{
    protected $client;
    protected $evaluation;

    public function __construct(Client $client, Evaluation $evaluation)
    {
        $this->client = $client;
        $this->evaluation = $evaluation;
    }

    public function getEvaluation()
    {
        return $this->evaluation;
    }
}