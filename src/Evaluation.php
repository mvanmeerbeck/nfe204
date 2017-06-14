<?php

namespace Nfe204;

class Evaluation
{
    protected $confusionMatrix = [];

    public function addPrediction($predictedClass, $realClass)
    {
        if (!isset($this->confusionMatrix[$realClass])) {
            $this->confusionMatrix[$realClass] = [];
        }

        if (!isset($this->confusionMatrix[$realClass][$predictedClass])) {
            $this->confusionMatrix[$realClass][$predictedClass] = 0;
        }

        $this->confusionMatrix[$realClass][$predictedClass]++;
    }

    public function getRecall($class)
    {
        $truePositive = 0;
        $falsePositive = 0;

        foreach ($this->confusionMatrix as $realClass => $predictedClasses) {
            foreach ($predictedClasses as $predictedClass => $count) {
                if ($predictedClass === $class && $realClass === $class) {
                    $truePositive += $count;
                } elseif ($realClass === $class) {
                    $falsePositive += $count;
                }
            }
        }

        return $truePositive / ($truePositive + $falsePositive);
    }

    public function getPrecision($class)
    {
        $truePositive = 0;
        $trueNegative = 0;

        foreach ($this->confusionMatrix as $realClass => $predictedClasses) {
            foreach ($predictedClasses as $predictedClass => $count) {
                if ($predictedClass === $class && $realClass === $class) {
                    $truePositive += $count;
                } elseif ($predictedClass === $class) {
                    $trueNegative += $count;
                }
            }
        }

        return $truePositive / ($truePositive + $trueNegative);
    }
}
