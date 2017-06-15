<?php

namespace Nfe204;

class Evaluation
{
    const MICRO_AVERAGE = 1;
    const MACRO_AVERAGE = 2;
    const TRUE_POSITIVE = 'TP';
    const TRUE_NEGATIVE = 'TN';
    const FALSE_POSITIVE = 'FP';
    const FALSE_NEGATIVE = 'FN';

    protected $confusionMatrix = [];
    protected $classes = [];

    public function addPrediction($predictedClass, $realClass)
    {
        $this
            ->addClass($predictedClass)
            ->addClass($realClass)
            ->fillMatrix();

        $this->confusionMatrix[$realClass][$predictedClass]++;
    }

    private function addClass($class)
    {
        if (!in_array($class, $this->classes)) {
            $this->classes[] = $class;
        }

        sort($this->classes);

        return $this;
    }

    private function fillMatrix()
    {
        foreach ($this->classes as $class1) {
            if (!array_key_exists($class1, $this->confusionMatrix)) {
                $this->confusionMatrix[$class1] = [];
            }

            foreach ($this->classes as $class2) {
                if (!array_key_exists($class2, $this->confusionMatrix[$class1])) {
                    $this->confusionMatrix[$class1][$class2] = 0;
                }
            }
        }

        return $this;
    }

    private function buildClassMatrices()
    {
        $matrices = [];

        foreach ($this->classes as $class) {
            $matrices[$class] = [
                self::TRUE_POSITIVE => 0,
                self::TRUE_NEGATIVE => 0,
                self::FALSE_POSITIVE => 0,
                self::FALSE_NEGATIVE => 0,
            ];
        }

        foreach ($this->confusionMatrix as $realClass => $classes) {
            foreach ($classes as $predictedClass => $count) {
                if ($realClass === $predictedClass) {
                    $matrices[$realClass][self::TRUE_POSITIVE] += $count;
                } else {
                    $matrices[$realClass][self::TRUE_NEGATIVE] += $count;
                    $matrices[$predictedClass][self::FALSE_POSITIVE] += $count;
                }
            }
        }

        return $matrices;
    }

    public function getRecall($average = self::MACRO_AVERAGE)
    {
        if (self::MACRO_AVERAGE === $average) {
            $recall = 0;

            foreach ($this->buildClassMatrices() as $matrix) {

            }
        }
    }

//    public function getRecall($class, $average)
//    {
//        $truePositive = 0;
//        $falsePositive = 0;
//
//        foreach ($this->confusionMatrix as $realClass => $predictedClasses) {
//            foreach ($predictedClasses as $predictedClass => $count) {
//                if ($predictedClass === $class && $realClass === $class) {
//                    $truePositive += $count;
//                } elseif ($realClass === $class) {
//                    $falsePositive += $count;
//                }
//            }
//        }
//
//        return $truePositive / ($truePositive + $falsePositive);
//    }
//
//    public function getPrecision($class, $average)
//    {
//        $truePositive = 0;
//        $trueNegative = 0;
//
//        foreach ($this->confusionMatrix as $realClass => $predictedClasses) {
//            foreach ($predictedClasses as $predictedClass => $count) {
//                if ($predictedClass === $class && $realClass === $class) {
//                    $truePositive += $count;
//                } elseif ($predictedClass === $class) {
//                    $trueNegative += $count;
//                }
//            }
//        }
//
//        return $truePositive / ($truePositive + $trueNegative);
//    }
}
