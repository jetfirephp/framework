<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Redirect
 * @package JetFire\Framework\System
 */
class Redirect extends RedirectResponse{


    /**
     * @param string $url
     * @param int $status
     * @param array $headers
     */
    public function __construct($url = ROOT, $status = 302, $headers = array()){
        parent::__construct($url,$status,$headers);
    }

    /**
     * @param null $to
     * @param array $params
     * @param int $code
     * @return $this|\Symfony\Component\HttpFoundation\Response
     */
    public function redirect($to = null,$params = [],$code = 302){
        if (is_null($to))
            return $this;
        return $this->to($to,$params,$code);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function back(){
        $this->setTargetUrl(App::getInstance()->get('request')->getServer()->get('HTTP_REFERER'));
        return $this->send();
    }

    /**
     * @param $to
     * @param array $params
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function to($to,$params = [],$code = 302){
        $this->setTargetUrl(App::getInstance()->get('response')->getView()->path($to,$params));
        $this->setStatusCode($code);
        return $this->send();
    }

    /**
     * @param $url
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function url($url,$code = 302){
        $this->setTargetUrl($url);
        $this->setStatusCode($code);
        return $this->send();
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];
        $session = App::getInstance()->get('session')->getSession();
        foreach ($key as $k => $v)
            $session->flash($k, $v);
        return $this;
    }

    /**
     * @param array $cookies
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire
     * @return $this
     */
    public function withCookie($key,$value,$expire = 0)
    {
        $this->headers->setCookie(new Cookie($key,$value,$expire));
        return $this;
    }

} 