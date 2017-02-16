<?php

namespace JetFire\Framework\Providers;


use JetFire\Autoloader\Autoload;

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
     * @param Autoload $loader
     * @param array $loads
     */
    public function init(Autoload $loader, $loads = []){
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