<?php

namespace JetFire\Framework\Providers;


use Doctrine\ORM\Tools\Console\ConsoleRunner;
use JetFire\Db\Model;
use Symfony\Component\Console\Application;

class ConsoleProvider extends Provider{

    private $cli;

    public function __construct(Application $cli,$commands = []){
        $this->cli = $cli;
        foreach($commands as $command){
            $this->cli->add($this->get($command));
        }
    }

    public function ormCommands($orm){
        if(is_array($orm) && in_array('doctrine',$orm)){
            $helperSet = ConsoleRunner::createHelperSet(Model::orm('doctrine')->getOrm());
            $this->cli->setCatchExceptions(true);
            $this->cli->setHelperSet($helperSet);
            ConsoleRunner::addCommands($this->cli);
        }
    }

    public function run(){
        $this->cli->run();
    }

} 