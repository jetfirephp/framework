<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\System\Mail;
use JetFire\Mailer\MailerInterface;

/**
 * Class MailProvider
 * @package JetFire\Framework\Providers
 */
class MailProvider extends Provider
{

    /**
     * @var
     */
    protected $mailer;
    /**
     * @var
     */
    protected $mail;
    /**
     * @var
     */
    protected $config;

    /**
     * @param $config
     * @param $params
     */
    public function initMailer($config, $params)
    {
        $this->config = $config;
        $this->mailer = $config['mailers'][$config['use']]['class'];
        $this->app->addRule($this->mailer, [
            'shared' => true,
            'construct' => [array_merge($params, $config['mailers'][$config['use']])]
        ]);
    }

    /**
     * @param $mail
     */
    public function initMail($mail)
    {
        $this->mail = $mail;
        $this->app->addRule($this->mail, [
            'shared' => true,
            'construct' => [$this->app],
            'substitutions' => ['JetFire\Mailer\MailerInterface' => ['instance' => $this->mailer]]
        ]);
    }


    /**
     * @param null $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        return is_null($key)
            ? $this->config
            : $this->config[$key];
    }

    /**
     * @return MailerInterface
     */
    public function getMailer()
    {
        return $this->app->get($this->mailer);
    }

    /**
     * @return Mail
     */
    public function getMail()
    {
        return $this->app->get($this->mail);
    }

} 