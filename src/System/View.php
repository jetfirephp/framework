<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Template\View as TemplateView;

/**
 * Class View
 * @package JetFire\Framework\System
 */
class View extends TemplateView
{

    /**
     * @var App
     */
    private $app;

    /**
     * View constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param $path
     * @param array $data
     */
    public function render($path, $data = [])
    {
        $viewDir = is_array($viewDir = $this->app->get('routing')->getRouter()->route->getTarget('view_dir')) ? $viewDir : [$viewDir];
        $this->setPath($viewDir);
        $this->setExtension($this->app->data['template_extension']);
        if(is_array($path)){
            $this->setContent($path);
        }else {
            foreach ($viewDir as $dir) {
                if (is_file(rtrim($dir, '/') . '/' . ltrim($path, '/') . $this->app->data['template_extension'])) {
                    $this->setTemplate($path);
                }
            }
        }
        $flash = $this->app->get('session')->getSession()->allFlash();
        foreach ($flash as $key => $content) {
            $data[$key] = $content;
        }
        $this->addData($data);
        return $this->app->get('template')->getTemplate()->render($this);
    }


    /**
     * @param null $path
     * @param array $params
     * @return mixed
     */
    public function path($path = null, $params = [])
    {
        return (!is_null($path))
            ? $this->app->get('routing')->getCollection()->getRoutePath($path, $params)
            : $this->app->get('request')->root();
    }

} 