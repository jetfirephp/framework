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
     * @var App
     */
    protected $app;

    /**
     * Controller constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param $path
     * @param array $data
     * @return mixed
     */
    public function render($path,$data = []){
        return $this->app->get('response')->getView()->render($path,$data);
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    public function json($data){
        /** @var Response $response */
        $response = $this->app->get('routing')->getResponse();
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
            return $this->app->get('response')->getRedirect();
        return $this->app->get('response')->getRedirect()->to($to,$params,$code);
    }

    /**
     * @return mixed
     */
    public function response(){
        return $this->app->get('routing')->getResponse();
    }



}
