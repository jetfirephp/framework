<?php

namespace JetFire\Framework\Factory;


class Session {

    private static $instance;

    public static function getInstance(){
        if(is_null(self::$instance))
            self::$instance = app('request')->getSession();
        return self::$instance;
    }

} 