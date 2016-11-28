<?php

namespace JetFire\Framework\Providers;


use JetFire\Autoloader\Autoload;
use JetFire\Framework\App;

/**
 * Class AutoloadProvider
 * @package JetFire\Framework\Providers
 */
class AutoloadProvider extends Provider{

    /**
     * @var Autoload
     */
    private $loader;

    /**
     * AutoloadProvider constructor.
     * @param App $app
     * @param Autoload $loader
     * @param array $loads
     */
    public function __construct(App $app, Autoload $loader, $loads = []){
        parent::__construct($app);
        $this->loader = $loader;
        foreach ($loads['namespaces'] as $prefix => $namespace)
            $this->loader->addNamespace($prefix,ROOT . DIRECTORY_SEPARATOR . ltrim($namespace,'/'));
        foreach ($loads['classes'] as $class => $path)
            $this->loader->addClass($class,ROOT. DIRECTORY_SEPARATOR . ltrim($path,'/'));
        $this->loader->register();
    }

    /**
     * @return Autoload
     */
    public function getLoader(){
        return $this->loader;
    }

} 