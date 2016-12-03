<?php

namespace JetFire\Framework\System;

use Symfony\Component\HttpFoundation\Cookie as HttpCookie;

/**
 * Class Cookie
 * @package JetFire\Framework\System
 * @method mixed all()
 * @method mixed keys()
 * @method mixed replace($parameters = [])
 * @method mixed set($key, $value)
 * @method mixed add($parameters = [])
 * @method mixed get($key,$default = null, $deep = false)
 * @method mixed has($key)
 * @method mixed remove($key)
 */
class Cookie {

    /**
     * @var $this
     */
    private static $instance;
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    private $cookies;
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response){
        $this->cookies = $request->getCookies();
        $this->response = $response;
        self::$instance = $this;
    }

    /**
     * @return mixed
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getCookies(){
        return $this->cookies;
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
    public function init($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true){
        $this->response->headers->setCookie(new HttpCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly));
    }

    /**
     * @param $name
     */
    public function destroy($name){
        $this->response->headers->clearCookie($name);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name,$args){
        return call_user_func_array([$this->getCookies(),$name],$args);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name,$args){
        return call_user_func_array([self::getInstance()->getCookies(),$name],$args);
    }

} 