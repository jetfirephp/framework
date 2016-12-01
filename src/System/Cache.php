<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Framework\Providers\CacheProvider;


/**
 * Class Cache
 * @package JetFire\Framework\Factories
 */
class Cache
{

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var Cache
     */
    private static $instance;

    /**
     * @param CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
        self::$instance = $this;
    }

    /**
     * @return $this
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param null $driver
     * @return mixed
     */
    public function find($driver = null)
    {
        return $this->cacheProvider->getCache($driver);
    }

    /**
     * @param $driver
     * @param $key
     * @return mixed
     */
    public function get($driver, $key = null)
    {
        return (is_null($key)) ? $this->find()->fetch($driver) : $this->find($driver)->fetch($key);
    }

    /**
     * @param $driver
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($driver, $key, $value = null)
    {
        return (is_null($value)) ? $this->find()->save($driver, $key) : $this->find($driver)->save($key, $value);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getInstance(), $name], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->cacheProvider->getCache(), $name], $arguments);
    }
} 