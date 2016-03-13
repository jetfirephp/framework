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
            self::$instance = new self;
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
        $this->setData($data);
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

    /**
     * @param $value
     * @return string
     */
    public function asset($value){
        if (substr($value, -3) == '.js' && is_file(ROOT.'/public/js/'.$value)) return ROOT.'/public/js/'.$value;
        if (substr($value, -4) == '.css' && is_file(ROOT.'/public/css/'.$value)) return ROOT.'/public/css/'.$value;
        if ((substr($value, -4) == '.png' || substr($value, -4) == '.jpg' ||  substr($value, -5) == '.jpeg' ||  substr($value, -4) == '.gif') && is_file(ROOT.'/public/img/'.$value))return ROOT.'/public/img/'.$value;
        return ROOT.'/public/'.$value;
    }

} 