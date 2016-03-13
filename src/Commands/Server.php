<?php

namespace JetFire\Framework\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Server extends Command{


    protected function configure()
    {
        $this
            ->setName('server:run')
            ->setDescription('Run local server')
            ->addArgument('port', InputArgument::OPTIONAL, 'Define your port')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $port = $input->getArgument('port');
        if ($port) {
            $output->writeln('Server running in http://localhost:'.$port.'/');
            echo shell_exec('php -S localhost:'.$port);
        } else {
            $output->writeln('Server running in http://localhost:8888/');
            echo shell_exec('php -S localhost:8888');
        }

    }

} 