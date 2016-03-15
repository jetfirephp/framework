<?php

namespace JetFire\Framework\Factories;
use JetFire\Framework\App;


/**
 * Class Cache
 * @package JetFire\Framework\Factories
 */
class Cache {

    /**
     * @var
     */
    private static $instance;

    /**
     *
     */
    public function __construct(){
        if(is_null(self::$instance))
            self::$instance = App::getInstance()->get('cache')->getCache();
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = App::getInstance()->get('cache')->getCache();
        return self::$instance;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key){
        return self::getInstance()->fetch($key);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set($key,$value){
        return self::getInstance()->save($key,$value);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name,$args){
        return call_user_func_array([self::getInstance(),$name],$args);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name,$args){
        return call_user_func_array([self::getInstance(),$name],$args);
    }
} 