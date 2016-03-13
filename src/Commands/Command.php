<?php

namespace JetFire\Framework\Commands;

use JetFire\Framework\App;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Command extends SymfonyCommand{

    private $app;

    protected function getApp(){
        if(is_null($this->app))
            $this->app = App::getInstance();
        return $this->app;
    }

    protected function get($name,$construct = []){
        return $this->getApp()->get($name,$construct);
    }

    protected function addAlias($alias,$class){
        $this->getApp()->addAlias($alias,$class);
    }

    protected function register($name,$rule){
        $this->getApp()->addRule($name,$rule);
    }

    protected function registerMap($rules){
        $this->getApp()->addRules($rules);
    }


} 