<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Http\Session;
use JetFire\Validator\Validator;
use JetFire\Http\Request as HttpRequest;

/**
 * Class Request
 * @package JetFire\Framework\System
 */
class Request extends HttpRequest{

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
     * @param Session $session
     */
    public function __construct(Session $session){
        parent::__construct();
        $this->setSession($session);
    }

    /**
     * @return array|bool
     */
    public function validate(){
        $args = func_num_args();
        $request = get_called_class();
        $response = false;
        if($args == 0) {
            if (method_exists($request, 'rules') && property_exists($request, 'messages'))
                $response = Validator::validatePost($request::rules(), $request::$messages);
            else if (method_exists($request, 'rules') && !property_exists($request, 'messages'))
                $response =  Validator::validatePost($request::rules());
        }
        if ($args == 1) {
            $param = func_get_arg(0);
            if(is_array($param)) {
                if (property_exists($request, 'messages'))
                    $response = Validator::validatePost($param, $request::$messages);
                else
                    $response = Validator::validatePost($param);
            }else{
                if (method_exists($request, $param) && property_exists($request, 'messages'))
                    $response = Validator::validatePost($request::$param(), $request::$messages);
                else if (method_exists($request, $param) && !property_exists($request, 'messages'))
                    $response =  Validator::validatePost($request::$param());
            }
        }
        if($args == 2)
            $response = Validator::validatePost(func_get_arg(0), func_get_arg(1));
        if($response['valid']) {
            $this->request->add($response['values']);
            return $response['valid'];
        }
        return $response;
    }

    /**
     * @return array
     */
    public function filled(){
        $values = [];
        foreach ($this->request->all() as $key => $post) {
            if (strtolower($key) != 'submit' && strtoupper($key) != '_METHOD' && strtolower($key) != '_token')
                if ($this->has($key))
                    $values[$key] = $this->input($key);
        }
        return $values;
    }

    /**
     * @param null $value
     * @param array $token
     * @return bool
     */
    public function submit($value = null,$token = []){
        if($this->method() == 'POST') {
            if (is_array($value)) $token = $value;
            if (!$this->hasXss($token)) return false;
            if (!is_array($value) && !is_null($value))
                return ($this->request->get($value)) ? true : false;
            return ($this->request->get('submit')) ? true : false;
        }
        return false;
    }

    /**
     * @param array $token
     * @return bool
     */
    private function hasXss($token = []){
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
                    $session->remove($name.'_token_');
                    if (is_null($referer)) return true;
                    else if (!is_null($referer) && $this->request->referer() == ROOT . $referer) return true;
                }
            }
        }
        return false;
    }

}