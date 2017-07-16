<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Routing\ResponseInterface;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Redirect
 * @package JetFire\Framework\System
 */
class Redirect extends RedirectResponse implements ResponseInterface
{

    /**
     * @var App
     */
    private $app;

    /**
     * @param $url
     * @param int $status
     * @param array $headers
     */
    public function __construct($url = ROOT, $status = 302, $headers = array())
    {
        parent::__construct($url, $status, $headers);
    }

    /**
     * @param App $app
     */
    public function setApp(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers = [])
    {
        foreach ($headers as $key => $content) {
            $this->headers->set($key, $content);
        }
    }
    
    /**
     * @param null $to
     * @param array $params
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirect($to = null, $params = [], $code = 302)
    {
        return (is_null($to))
            ? $this
            : $this->to($to, $params, $code);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function back()
    {
        $this->setTargetUrl($this->app->get('request')->getServer()->get('HTTP_REFERER'));
        return $this;
    }

    /**
     * @param $to
     * @param array $params
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function to($to, $params = [], $code = 302)
    {
        $this->setTargetUrl($this->app->get('response')->getView()->path($to, $params));
        $this->setStatusCode($code);
        return $this;
    }

    /**
     * @param $url
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function url($url, $code = 302)
    {
        (substr($url, 0, 4) !== "http")
            ? $this->setTargetUrl($this->app->get('request')->root() . '/' . ltrim($url, '/'))
            : $this->setTargetUrl($url);
        $this->setStatusCode($code);
        return $this;
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];
        $session = $this->app->get('session')->getSession();
        foreach ($key as $k => $v) {
            $session->flash($k, $v);
        }
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
    public function withCookie($key, $value, $expire = 0)
    {
        $this->headers->setCookie(new HttpCookie($key, $value, $expire));
        return $this;
    }
} 