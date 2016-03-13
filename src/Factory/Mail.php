<?php

namespace JetFire\Framework\Factory;


/**
 * Class Mail
 * @package JetFire\Framework\Factory
 */
class Mail {

    /**
     * @var
     */
    private static $instance;

    /**
     * @return mixed
     */
    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = app('mail')->getMailer();
        return self::$instance;
    }

    /**
     * @param $to
     * @param $subject
     * @param $content
     * @param null $file
     */
    public static function sendTo($to,$subject,$content,$file = null){
        $from = (isset(app()->data['app']['setting']['mail']['from']))
            ? app()->data['app']['setting']['mail']['from']
            : 'contact@jetfire.fr';
        $message = self::getInstance()->to($to)
            ->from($from)
            ->subject($subject)
            ->html($content);
        if(!is_null($file))
            $message->file($file);
        $message->send();
    }

    public static function __callStatic($name,$args){
        return self::getInstance()->$name($args);
    }

} 