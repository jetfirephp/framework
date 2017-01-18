<?php

namespace JetFire\Framework;

use JetFire\Di\Di;
use JetFire\Framework\System\Controller;

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
        if (is_null(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param array $config
     */
    public function load($config = [])
    {
        $this->config = $config;
        $this->register($this);
        foreach ($this->config['required_files'] as $file)
            if (file_exists($file)) require $file;
        foreach ($this->config['include_files'] as $key => $file)
            if (file_exists($file)) $this->data[$key] = include $file;
        $this->addRules($this->config['providers'], $this->data);
    }

    /**
     * @param $event
     * @param $value
     */
    public function emit($event, $value)
    {
        if (isset($this->data['app']['events']) && isset($this->data['app']['events'][$event])) {
            $event = $this->data['app']['events'][$event];
            if (!is_array($event)) $event = [$event];
            if (!is_array($value)) $value = [$value];
            /** @var Controller $controller */
            $controller = $this->get('JetFire\Framework\System\Controller');
            foreach ($event as $callbacks) {
                if ($callbacks['type'] == 'linear' && isset($callbacks['callback'])) {
                    $callback = explode('@', $callbacks['callback']);
                    if (class_exists($callback[0]) && method_exists($callback[0], $callback[1])) {
                        $controller->callMethod($callback[0], $callback[1], $value);
                    }
                } elseif ($callbacks['type'] == 'async' && isset($callbacks['route'])) {
                    $view = $this->get('response')->getView();
                    $method = (isset($callbacks['method'])) ? strtoupper($callbacks['method']) : 'GET';
                    $args = (isset($callbacks['args'])) ? array_merge([$value], $callbacks['args']) : [$value];
                    $path = $view->path($callbacks['route'], $args);
                    $client = $this->get('http')->getClient();
                    $client->requestAsync($method, $path, ['body' => $value]);
                }
            }
        }
    }

    /**
     *
     */
    public function boot()
    {
        foreach ($this->config['providers'] as $key => $provider) {
            if (isset($provider['boot']) && $provider['boot'])
                $this->get($provider['use']);
        }
    }

    /**
     *
     */
    public function fire()
    {
        try {
            if ($this->data['setting']['maintenance'])
                $this->get('system')->maintenance();
            else {
                $router = $this->get('routing')->getRouter();
                $router->run();
            }
        } catch (\Exception $e) {
            $this->get('system')->handleException($e);
        }
    }
} 