<?php

namespace Nfe204\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: mvanmeerbeck
 * Date: 08/06/17
 * Time: 14:39
 */
class SolrExportCommand extends Command
{
    public function configure()
    {
        $this->setName('nfe204:solr:export');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

    }
}