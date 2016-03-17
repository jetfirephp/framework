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

    public function handleError(){
        register_shutdown_function( "fatal_handler" );
    }

    private function fatal_handler() {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if( $error !== NULL) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];

            error_mail(format_error( $errno, $errstr, $errfile, $errline));
        }
    }
    private function format_error( $errno, $errstr, $errfile, $errline ) {
        $trace = print_r( debug_backtrace( false ), true );

        $content = "
  <table>
  <thead><th>Item</th><th>Description</th></thead>
  <tbody>
  <tr>
    <th>Error</th>
    <td><pre>$errstr</pre></td>
  </tr>
  <tr>
    <th>Errno</th>
    <td><pre>$errno</pre></td>
  </tr>
  <tr>
    <th>File</th>
    <td>$errfile</td>
  </tr>
  <tr>
    <th>Line</th>
    <td>$errline</td>
  </tr>
  <tr>
    <th>Trace</th>
    <td><pre>$trace</pre></td>
  </tr>
  </tbody>
  </table>";

        return $content;
    }

    public function maintenance(){
        $routing = $this->get('routing');
        $routing->getResponse()->setStatusCode(503);
        $routing->getRouter()->callResponse();
    }

} 