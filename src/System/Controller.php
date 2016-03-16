<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller {


    public function render($path,$data = []){
        return App::getInstance()->get('response')->getView()->render($path,$data);
    }

    public function json($data){
        $response = new JsonResponse($data);
        return $response->send();
    }

    public function redirect($to = null,$params = [],$code = 302){
        if (is_null($to))
            return App::getInstance()->get('response')->getRedirect();
        return App::getInstance()->get('response')->getRedirect()->to($to,$params,$code);
    }

    public function response(){
        return App::getInstance()->get('response');
    }



}
