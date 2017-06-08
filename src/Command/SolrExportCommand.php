<?php

namespace Nfe204\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{


    public function configure()
    {
        $this->setName('nfe204:solr:export');
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

    }
}