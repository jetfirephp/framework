<?php

namespace JetFire\Framework\Providers;

/**
 * Class TemplateProvider
 * @package JetFire\Framework\Providers
 */
class TemplateProvider extends Provider{

    /**
     * @var mixed
     */
    protected $template;
    /**
     * @var mixed
     */
    protected $config;

    /**
     * @param $template
     * @param $env
     */
    public function __construct($template = [],$env){
        $this->config = $template['engines'][$template['use']];
        if($env == 'dev'){
            $this->config['cache'] = false;
            $this->config['debug'] = true;
        }else
            $this->config['debug'] = false;
        $this->register($this->config['class'],[
            'shared' => true,
            'construct' => [$this->config],
        ]);
        $this->template = $this->config['class'];
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->get($this->template);
    }

    /**
     * @return mixed
     */
    public function getEngine(){
        return $this->getTemplate()->getTemplate('engine');
    }

    /**
     * @return mixed
     */
    public function getLoader(){
        return $this->getTemplate()->getTemplate('loader');
    }

    /**
     *
     */
    public function setExtensions(){
        foreach($this->config['functions'] as $extension)
            $this->getTemplate()->addExtension($this->get($extension));
    }

} 