<?php

namespace JetFire\Framework\Providers;
use JetFire\Routing\ResponseInterface;


/**
 * Class ResponseProvider
 * @package JetFire\Framework\Providers
 */
class ResponseProvider extends Provider
{

    /**
     * @var
     */
    protected $response;
    /**
     * @var
     */
    protected $redirect;
    /**
     * @var
     */
    protected $view;

    /**
     * @param $response
     */
    public function setResponseClass($response)
    {
        $this->app->addRule($response, [
            'shared' => true,
        ]);
        $this->response = $response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->app->register($response, $this->response);
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->app->get($this->response);
    }

    /**
     * @param $redirect
     */
    public function setRedirectClass($redirect)
    {
        $this->app->addRule($redirect, [
            'shared' => true,
            'call' => [
                'setApp' => [$this->app],
            ]
        ]);
        $this->redirect = $redirect;
    }

    /**
     * @param ResponseInterface $redirect
     */
    public function setRedirect(ResponseInterface $redirect)
    {
        $this->app->register($redirect, $this->redirect);
    }

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->app->get($this->redirect);
    }

    /**
     * @param $view
     */
    public function setViewClass($view)
    {
        $this->app->addRule($view, [
            'shared' => true
        ]);
        $this->view = $view;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->app->get($this->view);
    }

} 