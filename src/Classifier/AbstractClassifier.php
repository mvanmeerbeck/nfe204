<?php

namespace Nfe204\Classifier;

use Elasticsearch\Client;

abstract class AbstractClassifier
{
    protected $client;
    protected $actualLabels = [];
    protected $predictedLabels = [];

    public function __construct(Client $client, $logPath)
    {
        $this->client = $client;
        $this->log = new \SplFileObject($logPath .  '/' . time(), 'w');
    }

    public function addPrediction($actualLabel, $predictedLabel)
    {
        $this->actualLabels[] = $actualLabel;
        $this->predictedLabels[] = $predictedLabel;

        $this->log->fputcsv([$actualLabel, $predictedLabel]);
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