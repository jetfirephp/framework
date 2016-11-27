<?php

namespace JetFire\Framework\Providers;

use DebugBar\Bridge\DoctrineCollector;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\Bridge\Twig\TwigProfilerDumperHtml;
use DebugBar\StandardDebugBar;
use Twig_Profiler_Profile;

/**
 * Class LogProvider
 * @package JetFire\Framework\Providers
 */
class DebugBarProvider extends Provider{

    /**
     * @var StandardDebugBar
     */
    private $debugBar;
    /**
     * @var \DebugBar\JavascriptRenderer
     */
    private $debugBarRenderer;

    /**
     * DebugBarProvider constructor.
     * @param StandardDebugBar $debugBar
     */
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

    /**
     * @param $value
     */
    public function debug($value){
        $this->debugBar["messages"]->addMessage($value);
    }

    /**
     * @return \DebugBar\DataCollector\DataCollectorInterface|mixed
     */
    public function messages(){
        return $this->debugBar["messages"];
    }

    /**
     * @param TwigProfilerDumperHtml $dumper
     * @param Twig_Profiler_Profile $profiler
     * @throws \DebugBar\DebugBarException
     */
    public function loadTwigDebugger(TwigProfilerDumperHtml $dumper, Twig_Profiler_Profile $profiler){
        $this->debugBar->addCollector(new TwigCollector($dumper,$profiler));
    }

    /**
     * @param $debug_stack
     * @throws \DebugBar\DebugBarException
     */
    public function loadDoctrineDebugger($debug_stack){
        $this->debugBar->addCollector(new DoctrineCollector($debug_stack));
    }

} 