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
     * @param $path
     * @param array $data
     * @return mixed
     */
    public function render($path, $data = [])
    {
        return $this->app->get('response')->getView()->render($path, $data);
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
     * @param array $classInstanceValues
     * @return mixed
     * @throws \Exception
     */
    public function callMethod($controller, $method, $methodArgs = [], $ctrlArgs = [], $classInstance = [], $classInstanceValues = [])
    {
        $reflectionMethod = new ReflectionMethod($controller, $method);
        $dependencies = [];
        if($reflectionMethod->getNumberOfParameters() != count($methodArgs)) {
            foreach ($reflectionMethod->getParameters() as $arg) {
                if (!is_null($arg->getClass())) {
                    if (in_array($arg->getClass()->name, $classInstance))
                        array_push($dependencies, $classInstanceValues[$arg->getClass()->name]);
                    else
                        array_push($dependencies, $this->app->get($arg->getClass()->name));
                }
            }
        }
        $dependencies = array_merge($dependencies, $methodArgs);
        return $reflectionMethod->invokeArgs($this->callController($this->app, $controller, $ctrlArgs, $classInstance, $classInstanceValues), $dependencies);
    }

    /**
     * @param App $app
     * @param $controller
     * @param array $ctrlArgs
     * @param array $classInstance
     * @param array $classInstanceValues
     * @return object
     * @throws \Exception
     * @internal param array $class
     * @internal param array $classValues
     */
    public function callController($app, $controller, $ctrlArgs = [], $classInstance = [], $classInstanceValues = [])
    {
        $reflector = new ReflectionClass($controller);
        if (!$reflector->isInstantiable())
            throw new \Exception('Controller [' . $controller . '] is not instantiable.');
        $constructor = $reflector->getConstructor();
        if (is_null($constructor))
            return $app->get($controller);
        $dependencies = $constructor->getParameters();
        $arguments = [];
        if($constructor->getNumberOfParameters() != count($ctrlArgs)) {
            foreach ($dependencies as $dep) {
                if (in_array($dep->getClass()->name, $classInstance))
                    array_push($arguments, $classInstanceValues[$dep->getClass()->name]);
                else
                    array_push($arguments, $app->get($dep->getClass()->name));
            }
        }
        $arguments = array_merge($arguments, $ctrlArgs);
        return $reflector->newInstanceArgs($arguments);
    }

}
