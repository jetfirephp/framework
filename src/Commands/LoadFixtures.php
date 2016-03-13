<?php

namespace JetFire\Framework\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Loader;


class LoadFixtures extends Command{

    protected function configure()
    {
        $this
            ->setName('load:data')
            ->setDescription('Load data into database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = 'Data loaded successfully';

        $loader = new Loader();
        $loader->loadFromDirectory(ROOT.'app/DataFixtures');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->get('database')->getProvider('doctrine')->em(), $purger);
        $executor->execute($loader->getFixtures());

        $output->writeln($text);
    }

} 