<?php

namespace Nfe204\Classifier;

interface ClassifierInterface
{
    public function predict(array $offer);
}