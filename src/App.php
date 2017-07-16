<?php

namespace JetFire\Framework;

use JetFire\Di\Di;
use JetFire\Routing\Router;

/**
 * Class App
 * @package JetFire\Framework
 */
class App extends Di
{

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
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return App|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param array $config
     */
    public function load($config = [])
    {
        $this->config = $config;
        $this->register($this);
        foreach ($this->config['required_files'] as $file) {
            if (file_exists($file)) require $file;
        }
        foreach ($this->config['include_files'] as $key => $file) {
            if (file_exists($file)) $this->data[$key] = $this->parseFile($file);
        }
        $this->addRules($this->config['providers'], $this->data);
    }

    /**
     * @param $file
     * @return mixed
     */
    private function parseFile($file){
        $ext = explode('.', $file);
        switch (end($ext)){
            case 'php':
                return include $file;
                break;
            case 'json':
                $json = file_get_contents($file);
                return json_decode($json, true);
                break;
            case 'xml':
                $xml = simplexml_load_file($file);
                $xml_array = unserialize(serialize(json_decode(json_encode((array) $xml), 1)));
                return $xml_array;
                break;
        }
        return $file;
    }

    /**
     *
     */
    public function boot()
    {
        foreach ($this->config['providers'] as $key => $provider) {
            if (isset($provider['boot']) && $provider['boot']) {
                $this->get($provider['use']);
            }
        }
    }

    /**
     *
     */
    public function fire()
    {
        try {
            if ($this->data['setting']['maintenance']) {
                $this->get('system')->maintenance();
            } else {
                /** @var Router $router */
                $router = $this->get('routing')->getRouter();
                $router->run();
            }
        } catch (\Exception $e) {
            $this->get('system')->handleException($e);
        }
    }
} 