<?php

namespace Nfe204;

class Evaluation
{
    const MICRO_AVERAGE = 1;
    const MACRO_AVERAGE = 2;

    protected $confusionMatrix = [];
    protected $classes = [];

    public function addPrediction($predictedClass, $realClass)
    {
        if (!in_array($predictedClass, $this->classes)) {
            $this->classes[] = $predictedClass;
        }

        if (!in_array($realClass, $this->classes)) {
            $this->classes[] = $realClass;
        }

        $this->updateMatrix();

        $this->confusionMatrix[$realClass][$predictedClass]++;
    }

    private function updateMatrix()
    {
        foreach ($this->classes as $class) {
            if (!array_key_exists($class, $this->confusionMatrix)) {
                $this->confusionMatrix[$class] = [];
            }

            foreach ($this->classes as $class2) {
                if (!array_key_exists($class2, $this->confusionMatrix[$class])) {
                    $this->confusionMatrix[$class][$class2] = 0;
                }
            }
        }
    }

    public function getRecall($class, $average)
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

    public function getPrecision($class, $average)
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
