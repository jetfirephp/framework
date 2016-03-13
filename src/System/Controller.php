<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;

class Controller {

    private $app;

    public function getApp(){
        if(is_null($this->app))
            $this->app = App::getInstance();
        return $this->app;
    }

    public function render($path,$data = []){
        return $this->getApp()->get('JetFire\Framework\System\View')->render($path,$data);
    }

    public function redirect($to = null,$params = [],$code = 302){
        if (is_null($to))
            return $this;
        return $this->to($to,$params,$code);
    }

    public function to($to,$params = [],$code = 302){
        $response = $this->getApp()->get('JetFire\Http\Redirect',[View::getInstance()->path($to,$params),$code]);
        return $response->send();
    }

    public function url($url,$code = 302){
        $response = $this->getApp()->get('JetFire\Http\Redirect',[$url,$code]);
        return $response->send();
    }

    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];
        $app = $this->getApp();
        foreach ($key as $k => $v)
            $app->get('session')->getSession()->flash($k, $v);
        return $this;
    }


}
