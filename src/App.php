<?php

namespace JetFire\Framework;

use JetFire\Di\Di;

/**
 * Class App
 * @package JetFire\Framework
 */
class App extends Di{

    /**
     * @var App|null
     */
    private static $instance = null;
    /**
     * @var array
     */
    private $config = [];
    /**
     * @var array
     */
    public $data = [];

    /**
     *
     */
    public function __construct(){
        self::$instance = $this;
    }

    /**
     * @return App|null
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param array $config
     */
    public function load($config = []){
        $this->config = $config;
        $this->instances[App::class] = $this;
        foreach($this->config['required_files'] as $file)
            if (file_exists($file)) require $file;
        foreach($this->config['include_files'] as $key => $file)
            if (file_exists($file))$this->data[$key] = include $file;
        $this->addRules($this->config['providers'],$this->data);
    }

    /**
     *
     */
    public function boot(){
        foreach ($this->config['providers'] as $key => $provider) {
            if (isset($provider['boot']) && $provider['boot'])
                $this->get($provider['use']);
        }
    }

    /**
     *
     */
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