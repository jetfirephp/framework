<?php

namespace JetFire\Framework\Providers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use JetFire\Db\Model;
use JetFire\Framework\App;
use Symfony\Component\Console\Application;

/**
 * Class ConsoleProvider
 * @package JetFire\Framework\Providers
 */
class ConsoleProvider extends Provider{

    /**
     * @var Application
     */
    private $cli;

    /**
     * ConsoleProvider constructor.
     * @param Application $cli
     * @param array $commands
     */
    public function init(Application $cli, $commands = []){
        $this->cli = $cli;
        foreach($commands as $command){
            $this->cli->add($this->app->get($command));
        }
    }

    /**
     * @param $orm
     */
    public function ormCommands($orm){
        if(is_array($orm) && in_array('doctrine',$orm)){
            /** @var EntityManager $em */
            $em = Model::orm('doctrine')->getOrm();
            $helperSet = ConsoleRunner::createHelperSet($em);
            $this->cli->setCatchExceptions(true);
            $this->cli->setHelperSet($helperSet);
            ConsoleRunner::addCommands($this->cli);
        }
    }

    /**
     * @throws \Exception
     */
    public function run(){
        $this->cli->run();
    }

} 