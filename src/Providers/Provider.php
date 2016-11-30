<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\App;

/**
 * Class Provider
 * @package JetFire\Framework\Providers
 */
class Provider {

    /**
     * @var App
     */
    protected $app;

    /**
     * Provider constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

} 