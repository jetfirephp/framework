<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Controller
 * @package JetFire\Framework\System
 */
class Controller {


    /**
     * @param $path
     * @param array $data
     * @return mixed
     */
    public function render($path,$data = []){
        return App::getInstance()->get('response')->getView()->render($path,$data);
    }

    /**
     * @param $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function json($data){
        $response = App::getInstance()->get('routing')->getResponse();
        $response->setContent(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
    }

    /**
     * @param null $to
     * @param array $params
     * @param int $code
     * @return mixed
     */
    public function redirect($to = null,$params = [],$code = 302){
        if (is_null($to))
            return App::getInstance()->get('response')->getRedirect();
        return App::getInstance()->get('response')->getRedirect()->to($to,$params,$code);
    }

    /**
     * @return mixed
     */
    public function response(){
        return App::getInstance()->get('routing')->getResponse();
    }



}
