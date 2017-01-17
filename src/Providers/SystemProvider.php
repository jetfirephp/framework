<?php

namespace JetFire\Framework\Providers;

use Kint;

/**
 * Class SystemProvider
 * @package JetFire\Framework\Providers
 */
class SystemProvider extends Provider
{

    /**
     * @var
     */
    private $env;

    /**
     * @param $env
     */
    public function init($env){
        $this->env = $env;
    }

    /**
     * @return mixed
     */
    public function getEnv(){
        return $this->env;
    }

    /**
     * @param $debugger
     */
    public function setDebugger($debugger)
    {
        if ($this->env == 'prod')
            Kint::enabled(false);
        else {
            if (isset($debugger['mode'])) Kint::enabled($debugger['mode']);
            Kint::$theme = isset($debugger['theme']) ? $debugger['theme'] : 'original';
        }
    }

    /**
     * @param $e
     */
    public function handleException($e)
    {
        $this->app->get('logger')->getLogger('main')->addError($e);
        if ($this->env == 'prod') {
            $routing = $this->app->get('routing');
            $routing->getResponse()->setStatusCode(500);
            $routing->getRouter()->callResponse();
        }
    }

    /**
     * @param $error
     */
    public function handleError($error)
    {
        if($this->env == 'dev') {
            (isset($error['displays'])) ? error_reporting($error['displays']) : error_reporting(-1);
            ini_set('display_startup_errors', true);
            ini_set('display_errors', 'stdout');
        }
    }

    /**
     *
     */
    public function maintenance()
    {
        $routing = $this->app->get('routing');
        $routing->getResponse()->setStatusCode(503);
        $routing->getRouter()->callResponse();
    }

} 