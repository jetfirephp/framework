<?php

namespace JetFire\Framework\Providers;


use JetFire\Autoloader\Autoload;

class AutoloadProvider extends Provider{

    private $loader;

    public function __construct(Autoload $loader,$loads = []){
        $this->loader = $loader;
        foreach ($loads['namespaces'] as $prefix => $namespace)
            $this->loader->addNamespace($prefix,ROOT . DIRECTORY_SEPARATOR . ltrim($namespace,'/'));
        foreach ($loads['classes'] as $class => $path)
            $this->loader->addClass($class,ROOT. DIRECTORY_SEPARATOR . ltrim($path,'/'));
        $this->loader->register();
    }

    public function getLoader(){
        return $this->loader;
    }

} 