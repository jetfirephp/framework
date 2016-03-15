<?php

namespace JetFire\Framework\Factory;
use JetFire\Framework\App;


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
     *
     */
    public function __construct(){
        if(is_null(self::$instance))
            self::$instance = App::getInstance()->get('request')->getFiles();
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = App::getInstance()->get('request')->getFiles();
        return self::$instance;
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
