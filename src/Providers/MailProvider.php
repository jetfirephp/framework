<?php

namespace JetFire\Framework\Providers;


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
     * @param $config
     */
    public function __construct($config){
        $this->config = $config;
        $this->register($config['mailers'][$config['use']]['class'],[
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
        return $this->get($this->mailer);
    }

    /**
     * @return mixed
     */
    public function getMail(){
        return $this->get($this->mailer)->getMail();
    }

    /**
     *
     */
    public function initMail(){
        Mail::init($this->getMailer());
    }

} 