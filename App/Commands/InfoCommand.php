#!/usr/bin/env php
<?php

namespace App\Commands;

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class InfoCommand extends Command
{
    protected $commandName = 'app:info';
    protected $commandDescription = "Display Application Information";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table
            ->setHeaders(['Server', 'App'])
            ->setRows([
                ['Version : ' . phpversion(), 'Memory : ' . memory_get_usage()],
                ['Name : ' . $_ENV['APP_NAME'], 'Version : ' . $_ENV['VERSION']]
            ]);
        $table->setVertical();
        $table->render();

        return Command::SUCCESS;
    }
}
