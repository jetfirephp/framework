<?php

namespace JetFire\Framework\Factory;


/**
 * Class File
 * @package JetFire\Framework\Factory
 */
class File {

    /**
     * @var
     */
    private static $instance;

    /**
     * @return mixed
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = app('request')->getFiles();
        return self::$instance;
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name,$args){
        return self::getInstance()->$name($args);
    }

} 