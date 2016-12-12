<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Framework\Providers\MailProvider;
use JetFire\Mailer\MailerInterface;

/**
 * Class Mail
 * @package JetFire\Framework\System
 */
class Mail
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var \JetFire\Mailer\Mail
     */
    private $mail;

    /**
     * @var Mail
     */
    private static $instance;

    /**
     * Mail constructor.
     * @param App $app
     * @param MailProvider $mailProvider
     */
    public function __construct(App $app, MailProvider $mailProvider)
    {
        $this->mail = $mailProvider->getMail();
        $this->app = $app;
        self::$instance = $this;
    }

    /**
     * @param $to
     * @param $subject
     * @param $content
     * @param null $file
     * @return mixed
     */
    public function sendTo($to, $subject, $content, $file = null)
    {
        $from = (isset($this->app->data['app']['settings']['mail']['from']))
            ? $this->app->data['app']['settings']['mail']['from']
            : 'contact@jetfire.fr';
        /** @var MailerInterface $message */
        $message = $this->mail->to($to)
            ->from($from)
            ->subject($subject)
            ->html($content);
        if (!is_null($file))
            $message->file($file);
        return $message->send();
    }

    /**
     * @return Mail
     */
    public static function getInstance(){
        return self::$instance;
    }

    /**
     * @return Mail|\JetFire\Mailer\Mail
     */
    public function getMail(){
        return $this->mail;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->mail,$name],$arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getInstance()->getMail(),$name],$arguments);
    }

} 