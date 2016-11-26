<?php

namespace JetFire\Framework\Providers;

use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\Bridge\Twig\TwigProfilerDumperArray;
use DebugBar\StandardDebugBar;
use Twig_Extension_Profiler;
use Twig_Profiler_Profile;

/**
 * Class LogProvider
 * @package JetFire\Framework\Providers
 */
class DebugBarProvider extends Provider{

    private $debugBar;
    private $debugBarRenderer;

    private $collectors = [
        'twig' => 'loadTwigDebugger',
        'doctrine' => 'loadDoctrineDebugger',
    ];

    public function __construct(StandardDebugBar $debugBar){
        $this->debugBar = $debugBar;
        $this->debugBarRenderer = $debugBar->getJavascriptRenderer();
    }

    /**
     * @return StandardDebugBar
     */
    public function getDebugBar()
    {
        return $this->debugBar;
    }

    /**
     * @param StandardDebugBar $debugBar
     */
    public function setDebugBar($debugBar)
    {
        $this->debugBar = $debugBar;
    }

    /**
     * @return \DebugBar\JavascriptRenderer
     */
    public function getDebugBarRenderer()
    {
        return $this->debugBarRenderer;
    }

    /**
     * @param \DebugBar\JavascriptRenderer $debugBarRenderer
     */
    public function setDebugBarRenderer($debugBarRenderer)
    {
        $this->debugBarRenderer = $debugBarRenderer;
    }

    public function debug($value){
        $this->debugBar["messages"]->addMessage($value);
    }

    public function loadCollectors($collectors = []){
        foreach ($collectors as $collector) {
            call_user_func([$this, $this->collectors[$collector]]);
        }
    }

    public function loadTwigDebugger(){
        $dumper = new TwigProfilerDumperArray();
        $profiler = new Twig_Profiler_Profile();
        $this->get('template')->getTemplate()->addExtension(new Twig_Extension_Profiler($profiler));
        $this->debugBar->addCollector(new TwigCollector($dumper,$profiler));
    }

    public function loadDoctrineDebugger(){

    }

} 