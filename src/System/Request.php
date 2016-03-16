<?php

namespace JetFire\Framework\System;

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
            if ( property_exists($request, 'messages'))
                $response = Validator::validatePost(func_get_arg(0), $request::$messages);
            else
                $response = Validator::validatePost(func_get_arg(0));
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
            if ($key != 'submit' && $key != '_METHOD' && $key != '_token')
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
        if(is_array($value))$token = $value;
        if(!isset($token['token'])) {
            if (!isset($token['time'])) $token['time'] = 600;
            if (!isset($token['name'])) $token['name'] = '';
            if (!isset($token['referer'])) $token['referer'] = null;
            if (!$this->isToken($token['time'], $token['name'], $token['referer'])) return false;
        }
        if(!is_array($value) && !is_null($value))
            return (isset($_POST[$value]))?true:false;
        return (isset($_POST['submit']))?true:false;
    }

    /**
     * @param $time
     * @param string $name
     * @param null $referer
     * @return bool
     */
    private function isToken($time, $name = '', $referer = null)
    {
        if (!is_null($this->session()->get($name . '_token')) && !is_null($this->session()->get($name . '_token_time')) && $this->request->get($name . '_token') != '') {
            if ($this->session()->get($name . '_token') == $this->request->get($name . '_token')) {
                if ($this->session()->get($name . '_token_time') >= (time() - $time)) {
                    if (is_null($referer)) return true;
                    else if (!is_null($referer) && $this->server->get('HTTP_REFERER') == $referer) return true;
                    $this->session()->remove($name . '_token');
                    $this->session()->remove($name . '_token_time');
                }
            }
        }
        return false;
    }

}