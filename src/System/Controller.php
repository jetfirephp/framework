<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class Controller
 * @package JetFire\Framework\System
 */
class Controller
{

    /**
     * @var App
     */
    protected $app;

    /**
     * Controller constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param $path
     * @param array $data
     * @return mixed
     */
    public function render($path, $data = [])
    {
        return $this->app->get('response')->getView()->render($path, $data);
    }

    /**
     * @return mixed
     */
    public function notFound()
    {
        $routing = $this->app->get('routing');
        $routing->getResponse()->setStatusCode(404);
        $routing->getRouter()->callResponse();
        exit;
    }

    /**
     * @param null $to
     * @param array $params
     * @param int $code
     * @return boolean
     */
    public function redirect($to = null, $params = [], $code = 302)
    {
        if (is_null($to))
            return $this->app->get('response')->getRedirect();
        return $this->app->get('response')->getRedirect()->to($to, $params, $code);
    }

    /**
     * @param $controller
     * @param $method
     * @param array $methodArgs
     * @param array $ctrlArgs
     * @param array $classInstance
     * @return mixed | null
     * @throws \Exception
     */
    public function callMethod($controller, $method, $methodArgs = [], $ctrlArgs = [], $classInstance = [])
    {
        if (class_exists($controller) && method_exists($controller, $method)) {
            $reflectionMethod = new ReflectionMethod($controller, $method);
            $dependencies = [];
            foreach ($reflectionMethod->getParameters() as $arg) {
                if (isset($methodArgs[$arg->name]))
                    array_push($dependencies, $methodArgs[$arg->name]);
                else if (!is_null($arg->getClass())) {
                    if (isset($classInstance[$arg->getClass()->name]))
                        array_push($dependencies, $classInstance[$arg->getClass()->name]);
                    else
                        array_push($dependencies, $this->app->get($arg->getClass()->name));
                }
            }
            $dependencies = array_merge($dependencies, $methodArgs);
            return $reflectionMethod->invokeArgs($this->callController($controller, $ctrlArgs, $classInstance), $dependencies);
        }
        return null;
    }

    /**
     * @param $controller
     * @param array $ctrlArgs
     * @param array $classInstance
     * @return object
     * @throws \Exception
     */
    public function callController($controller, $ctrlArgs = [], $classInstance = [])
    {
        $reflector = new ReflectionClass($controller);
        if (!$reflector->isInstantiable())
            throw new \Exception('Controller [' . $controller . '] is not instantiable.');
        $constructor = $reflector->getConstructor();
        if (is_null($constructor))
            return $this->app->get($controller);
        $dependencies = [];
        foreach ($constructor->getParameters() as $arg) {
            if (isset($ctrlArgs[$arg->name]))
                array_push($dependencies, $ctrlArgs[$arg->name]);
            else if (isset($classInstance[$arg->getClass()->name]))
                array_push($dependencies, $classInstance[$arg->getClass()->name]);
            else
                array_push($dependencies, $this->app->get($arg->getClass()->name));
        }
        $dependencies = array_merge($dependencies, $ctrlArgs);
        return $reflector->newInstanceArgs($dependencies);
    }

}
