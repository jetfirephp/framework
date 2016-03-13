<?php

namespace JetFire\Framework\Providers;

use Kint;

class SystemProvider extends Provider{

    public function setDebugger($debugger){
        if($this->getApp()->data['config']['system']['environment'] == 'prod')
            Kint::enabled(false);
        else{
            if(isset($debugger['mode']))Kint::enabled($debugger['mode']);
            Kint::$theme = isset($debugger['theme'])?$debugger['theme']:'original';
        }
    }

    public function handleException($e){
        $this->get('logger')->getLogger('main')->addError($e);
        if($this->getApp()->data['config']['system']['environment'] == 'prod') {
            $routing = $this->get('routing');
            $routing->getResponse()->setStatusCode(500);
            $routing->getRouter()->callResponse();
        }
    }

    public function maintenance(){
        $routing = $this->get('routing');
        $routing->getResponse()->setStatusCode(503);
        $routing->getRouter()->callResponse();
    }

} 