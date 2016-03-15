<?php

namespace JetFire\Framework\System;
use JetFire\Framework\App;

/**
 * Class View
 * @package JetFire\Framework\System
 */
class View extends \JetFire\Template\View{

    /**
     * @var
     */
    private static $instance;

    /**
     * @return View
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = App::getInstance()->get('JetFire\Framework\System\View');
        return self::$instance;
    }

    /**
     * @param $path
     * @param array $data
     */
    public function render($path,$data = []){
        $app = App::getInstance();
        $this->setPath($app->get('routing')->getRouter()->route->getDetail()['block']);
        $this->setExtension($app->data['template_extension']);
        $this->setTemplate($path);
        $flash = $app->get('session')->getSession()->allFlash();
        foreach ($flash as $key => $content)
            (!isset($content[1]))
                ? $data[$key] = $content[0]
                : $data[$key] = $content;
        $this->addData($data);
        return $app->get('template')->getTemplate()->render($this);
    }


    /**
     * @param null $path
     * @param array $params
     * @return mixed
     */
    public function path($path = null,$params = []){
        $app = App::getInstance();
        if(!is_null($path))
            return $app->get('routing')->getCollection()->getRoutePath($path,$params);
        return $app->get('request')->root();
    }

} 