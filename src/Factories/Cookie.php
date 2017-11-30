<?php

namespace JetFire\Framework\Factories;

use JetFire\Framework\App;

/**
 * Class Cookie
 * @package JetFire\Framework\Factories
 */
class Cookie
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
            self::$instance = App::getInstance()->get('request')->getCookies();
        }
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = App::getInstance()->get('request')->getCookies();
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @param null $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public static function set($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        App::getInstance()->get('routing')->getResponse()->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly));
    }

    /**
     * @param $name
     */
    public static function destroy($name)
    {
        App::getInstance()->get('routing')->getResponse()->headers->clearCookie($name);
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