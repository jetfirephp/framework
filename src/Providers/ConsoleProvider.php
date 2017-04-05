<?php

namespace JetFire\Framework\Providers;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use JetFire\Db\Model;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class ConsoleProvider
 * @package JetFire\Framework\Providers
 */
class ConsoleProvider extends Provider
{

    /**
     * @var Application
     */
    protected $cli;
    /**
     * @var
     */
    protected $commands;

    /**
     * ConsoleProvider constructor.
     * @param Application $cli
     * @param array $commands
     */
    public function init(Application $cli, $commands = [])
    {
        $this->cli = $cli;
        $this->commands = $commands;
    }

    /**
     * @param $orm
     */
    public function ormCommands($orm)
    {
        if(is_array($orm)) foreach ($orm as $o) call_user_func_array([$this,$o.'Commands'],[]);

        $this->setCommands();
    }

    /**
     *
     */
    private function pdoCommands(){

    }

    /**
     *
     */
    private function redbeanCommands(){

    }

    /**
     *
     */
    private function doctrineCommands(){
        /** @var EntityManager $em */
        $em = Model::orm('doctrine')->getOrm();
        $helperSet = new HelperSet([
            'db' => new ConnectionHelper($em->getConnection()),
            'em' => new EntityManagerHelper($em),
            'dialog' => new \Symfony\Component\Console\Helper\QuestionHelper(),
        ]);
        $this->cli->setCatchExceptions(true);
        $this->cli->setHelperSet($helperSet);
        ConsoleRunner::addCommands($this->cli);
    }

    /**
     *
     */
    private function setCommands(){
        foreach ($this->commands['di'] as $command)
            $this->cli->add($this->app->get($command));
        foreach ($this->commands['new'] as $instance)
            $this->cli->add(new $instance);
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->cli->run();
    }

} 