<?php

namespace JetFire\Framework\Commands;

use JetFire\Framework\App;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Class Command
 * @package JetFire\Framework\Commands
 */
class Command extends SymfonyCommand{

    /**
     * @var App
     */
    protected $app;

    /**
     * Command constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct(null);
        $this->app = $app;
    }

} 