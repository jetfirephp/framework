<?php

namespace JetFire\Framework\Providers;

use DebugBar\Bridge\Twig\TwigProfilerDumperHtml;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;

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
     * @var
     */
    private $env;

    /**
     * @var mixed
     */
    private $engine;

    /**
     * @param array $template
     * @param $env
     */
    public function init($template = [],$env){
        $this->engine = $template['use'];
        $this->config = $template['engines'][$template['use']];
        $this->env = $env;
        if($this->env == 'dev'){
            $this->config['cache'] = false;
            $this->config['debug'] = true;
        }else
            $this->config['debug'] = false;
        $this->app->addRule($this->config['class'],[
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
        return $this->app->get($this->template);
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
            $this->getTemplate()->addExtension($this->app->get($extension));
    }


    /**
     * @param bool $enable
     */
    public function setDebugBar($enable = true){
        if($this->env == 'dev' && $enable){
            switch ($this->engine){
                case 'twig':
                    $dumper = new TwigProfilerDumperHtml();
                    $profiler = new Twig_Profiler_Profile();
                    $this->getTemplate()->addExtension(new Twig_Extension_Profiler($profiler));
                    $this->app->get('debug_toolbar')->loadTwigDebugger($dumper,$profiler);
                    break;
            }
        }
    }
} 