<?php

namespace JetFire\Framework\Providers;


use JetFire\Framework\App;
use JetFire\Mailer\Mail;

/**
 * Class MailProvider
 * @package JetFire\Framework\Providers
 */
class MailProvider extends Provider{

    /**
     * @var
     */
    protected $mailer;
    /**
     * @var
     */
    protected $config;

    /**
     * @param App $app
     * @param $config
     */
    public function __construct(App $app, $config){
        parent::__construct($app);
        $this->config = $config;
        $this->app->addRule($config['mailers'][$config['use']]['class'],[
            'shared' => true,
            'construct' => [array_merge($config['config'],$config['mailers'][$config['use']])]
        ]);
        $this->mailer = $config['mailers'][$config['use']]['class'];
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getConfig($key = null){
        return is_null($key)
            ? $this->config
            : $this->config[$key];
    }

    /**
     * @return mixed
     */
    public function getMailer(){
        return $this->app->get($this->mailer);
    }

    /**
     * @return mixed
     */
    public function getMail(){
        return $this->app->get($this->mailer)->getMail();
    }

    /**
     *
     */
    public function initMail(){
        Mail::init($this->getMailer());
    }

} 