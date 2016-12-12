<?php

namespace JetFire\Framework\System;

use JetFire\Framework\Providers\RoutingProvider;
use JetFire\Framework\Providers\SessionProvider;
use JetFire\Validation\Validation;
use JetFire\Http\Request as HttpRequest;

/**
 * Class Request
 * @package JetFire\Framework\System
 */
class Request extends HttpRequest
{
    /**
     * @param SessionProvider $sessionProvider
     * @param RoutingProvider $routingProvider
     */
    public function __construct(SessionProvider $sessionProvider, RoutingProvider $routingProvider)
    {
        parent::__construct();
        $this->setSession($sessionProvider->getSession());
        $this->attributes->set('routing', $routingProvider->getRouter());
    }


    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\FileBag
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getPost()
    {
        return $this->request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ServerBag
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->attributes->get('routing')->route;
    }

    /**
     * @return array|bool
     */
    public function validate()
    {
        $validate = ($this->method() == 'GET') ? 'validateGet' : 'validatePost';
        $validation = new Validation();
        $args = func_num_args();
        $request = get_called_class();
        $response = false;
        if ($args == 0) {
            if (method_exists($request, 'rules') && property_exists($request, 'messages'))
                $response = $validation->$validate($request::rules(), $request::$messages);
            else if (method_exists($request, 'rules') && !property_exists($request, 'messages'))
                $response = $validation->$validate($request::rules());
        }
        if ($args == 1) {
            $param = func_get_arg(0);
            if (is_array($param)) {
                if (property_exists($request, 'messages'))
                    $response = $validation->$validate($param, $request::$messages);
                else
                    $response = $validation->$validate($param);
            } else {
                if (method_exists($request, $param) && property_exists($request, 'messages'))
                    $response = $validation->$validate($request::$param(), $request::$messages);
                else if (method_exists($request, $param) && !property_exists($request, 'messages'))
                    $response = $validation->$validate($request::$param());
            }
        }
        if ($args == 2) {
            $response = $validation->$validate(func_get_arg(0), func_get_arg(1));
        }
        if ($response['valid'] === true) {
            $this->attributes->set('response_values', $response['values']);
            $this->request->add($response['values']);
            return true;
        }
        return $response;
    }

    /**
     * @return array
     */
    public function filled()
    {
        $values = [];
        foreach ($this->request->all() as $key => $post) {
            if (strtolower($key) != 'submit' && strtoupper($key) != '_METHOD' && strtolower($key) != '_token')
                if ($this->has($key))
                    $values[$key] = $this->input($key);
        }
        return $values;
    }

    /**
     * @return array
     */
    public function values()
    {
        return $this->attributes->get('response_values');
    }


    /**
     * @param null $value
     * @param array $token
     * @return bool
     */
    public function submit($value = null, $token = [])
    {
        if ($this->method() != 'GET') {
            if (is_array($value)) $token = $value;
            if (!$this->hasXss($token)) return false;
            if (!is_array($value) && !is_null($value))
                return ($this->request->get($value)) ? true : false;
            return ($this->request->has('submit')) ? true : false;
        }
        return false;
    }

    /**
     * @param array $token
     * @return bool
     */
    public function hasXss($token = [])
    {
        if (!isset($token['token'])) {
            if (!isset($token['time'])) $token['time'] = 600;
            if (!isset($token['name'])) $token['name'] = '';
            if (!isset($token['referer'])) $token['referer'] = null;
            if (!$this->isToken($token['time'], $token['name'], $token['referer'])) return false;
        }
        return true;
    }

    /**
     * @param $time
     * @param string $name
     * @param null $referer
     * @return bool
     */
    private function isToken($time, $name = '', $referer = null)
    {
        $session = $this->getSession();
        if ($session->get($name . '_token_') && $this->request->get($name . '_token') != '') {
            if ($session->get($name . '_token_')['key'] == $this->request->get($name . '_token')) {
                if ($session->get($name . '_token_')['time'] >= (time() - $time)) {
                    $session->remove($name . '_token_');
                    if (is_null($referer)) return true;
                    else if (!is_null($referer) && $this->referer() == ROOT . $referer) return true;
                }
            }
        }
        return false;
    }

}