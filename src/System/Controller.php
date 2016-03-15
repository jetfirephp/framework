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
        return $this->getApp()->get('response')->getView()->render($path,$data);
    }

    public function redirect($to = null,$params = [],$code = 302){
        if (is_null($to))
            return $this->getApp()->get('response')->getRedirect();
        return $this->getApp()->get('response')->getRedirect()->to($to,$params,$code);
    }

}
