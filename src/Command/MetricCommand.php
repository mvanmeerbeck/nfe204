<?php

namespace Nfe204\Command;

use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;
use Phpml\Metric\ConfusionMatrix;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MetricCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('metric')
            ->addArgument('log', InputArgument::REQUIRED)
            ->addOption('accuracy', 'a', InputOption::VALUE_NONE)
            ->addOption('matrix', 'm', InputOption::VALUE_NONE)
            ->addOption('report', 'r', InputOption::VALUE_NONE);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $log = new \SplFileObject($input->getArgument('log'), 'r');
        $log->setFlags(\SplFileObject::READ_CSV);

        $actualLabels = [];
        $predictedLabels = [];

        foreach ($log as $data) {
            if (2 === count($data)) {
                $actualLabels[] = strval($data[0]);
                $predictedLabels[] = strval($data[1]);
            }
        }

        if ($input->getOption('accuracy')) {
            $accuracy = Accuracy::score($actualLabels, $predictedLabels);
            $output->writeln("accuracy: $accuracy");
        }

        if ($input->getOption('matrix')) {
            $confusionMatrix = ConfusionMatrix::compute($actualLabels, $predictedLabels);

            print_r($confusionMatrix);
        }

        if ($input->getOption('report')) {
            $report = new ClassificationReport($actualLabels, $predictedLabels);

            $average = $report->getAverage();

            foreach ($average as $metric => $value) {
                $output->writeln("$metric: $value");
            }
        }
    }
}