<?php

namespace JetFire\Framework\Providers;


use JetFire\Autoloader\Autoload;

class AutoloadProvider extends Provider{

    private $loader;

    public function __construct(Autoload $loader,$loads = []){
        $this->loader = $loader;
        $this->loader->setNamespaces($loads['namespaces']);
        $this->loader->setClassCollection($loads['classes']);
        $this->loader->register();
    }

    public function getLoader(){
        return $this->loader;
    }

} 