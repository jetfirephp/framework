<?php

namespace JetFire\Framework\Factory;


/**
 * Class Cache
 * @package JetFire\Framework\Factory
 */
class Cache {

    /**
     * @var
     */
    private static $instance;

    /**
     * @return mixed
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = app('cache')->getCache();
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
        if(isset($args[2]))
            return self::getInstance()->$name($args[0],$args[1],$args[2]);
        elseif(isset($args[1]))
            return self::getInstance()->$name($args[0],$args[1]);
        return self::getInstance()->$name($args[1]);
    }
} 