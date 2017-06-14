<?php

namespace Nfe204\Classifier;

class Classifier extends AbstractClassifier implements ClassifierInterface
{
    public function predict(array $offer)
    {
        return true;
    }
}