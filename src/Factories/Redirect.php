<?php

namespace JetFire\Framework\Factories;

use JetFire\Framework\App;


/**
 * Class Redirect
 * @package JetFire\Framework\Factories
 */
class Redirect
{

    /**
     * @var
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        if (is_null(self::$instance)) {
            self::$instance = App::getInstance()->get('response')->getRedirect();
        }
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = App::getInstance()->get('response')->getRedirect();
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        return call_user_func_array([self::getInstance(), $name], $args);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        return call_user_func_array([self::getInstance(), $name], $args);
    }
} 