<?php

namespace JetFire\Framework\System;

use JetFire\Framework\App;
use JetFire\Mailer\Mail as MailComponent;
use JetFire\Mailer\MailerInterface;

/**
 * Class Mail
 * @package JetFire\Framework\System
 */
class Mail extends MailComponent
{
    /**
     * @var App
     */
    private $app;

    /**
     * Mail constructor.
     * @param App $app
     * @param MailerInterface $mailer
     */
    public function __construct(App $app, MailerInterface $mailer)
    {
        parent::__construct($mailer);
        $this->app = $app;
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
        $message = $this->to($to)
            ->from($from)
            ->subject($subject)
            ->html($content);
        if (!is_null($file))
            $message->file($file);
        return $message->send();
    }

} 