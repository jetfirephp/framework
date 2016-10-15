<?php

namespace JetFire\Framework\System;
use JetFire\Framework\App;
use JetFire\Template\View as TemplateView;
/**
 * Class View
 * @package JetFire\Framework\System
 */
class View extends TemplateView{

    /**
     * @param $path
     * @param array $data
     */
    public function render($path,$data = []){
        $app = App::getInstance();
        $this->setPath($app->get('routing')->getRouter()->route->getTarget('view_dir'));
        $this->setExtension($app->data['template_extension']);
        (!is_array($path) && is_file($app->get('routing')->getRouter()->route->getTarget('view_dir').$path.$app->data['template_extension']))
            ? $this->setTemplate($path)
            : $this->setContent($path);
        $flash = $app->get('session')->getSession()->allFlash();
        foreach ($flash as $key => $content)
            $data[$key] = $content;
        $this->addData($data);
        return $app->get('template')->getTemplate()->render($this);
    }


    /**
     * @param null $path
     * @param array $params
     * @param string $subdomain
     * @return mixed
     */
    public function path($path = null,$params = [],$subdomain = ''){
        $app = App::getInstance();
        if(!is_null($path))
            return $app->get('routing')->getCollection()->getRoutePath($path,$params,$subdomain);
        return $app->get('request')->root();
    }

} 