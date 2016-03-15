<?php

namespace JetFire\Framework\Providers;


/**
 * Class ResponseProvider
 * @package JetFire\Framework\Providers
 */
class ResponseProvider extends Provider{

    /**
     * @var
     */
    private $response;
    /**
     * @var
     */
    private $redirect;
    /**
     * @var
     */
    private $view;

    /**
     * @param $response
     */
    public function setResponseClass($response){
        $this->register($response,[
            'shared' => true,
        ]);
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse(){
        return $this->get($this->response);
    }

    /**
     * @param $redirect
     */
    public function setRedirectClass($redirect){
        $this->register($redirect,[
            'shared' => true,
        ]);
        $this->redirect = $redirect;
    }

    /**
     * @return mixed
     */
    public function getRedirect(){
        return $this->get($this->redirect);
    }


    /**
     * @param $view
     */
    public function setViewClass($view){
        $this->register($view,[
            'shared' => true,
        ]);
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getView(){
        return $this->get($this->view);
    }

} 