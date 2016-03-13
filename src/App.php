<?php

namespace JetFire\Framework;

use JetFire\Di\Di;

class App extends Di{

    private static $instance = null;
    private $config = [];

    public $data = [];

    public function __construct(){
        self::$instance = $this;
    }

    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    public function load($config = []){
        $this->config = $config;
        foreach($this->config['required_files'] as $file)
            if (file_exists($file)) require $file;
        foreach($this->config['include_files'] as $key => $file)
            if (file_exists($file))$this->data[$key] = include $file;
        $this->addRules($this->config['providers'],$this->data);
    }

    public function boot(){
        foreach ($this->config['providers'] as $key => $provider) {
            if (isset($provider['boot']) && $provider['boot'])
                $this->get($provider['use']);
        }
    }

    public function fire(){
        try{
            if($this->data['config']['system']['maintenance'])
                $this->get('system')->maintenance();
            else {
                $router = $this->get('routing')->getRouter();
                $router->run();
            }
        }catch (\Exception $e){
            $this->get('system')->handleException($e);
        }
    }
} 