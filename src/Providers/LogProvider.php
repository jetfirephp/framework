<?php

namespace JetFire\Framework\Providers;

use JetFire\Mailer\SwiftMailer\SwiftMailer;
use Monolog\Logger;
use PDO;

class LogProvider extends Provider{

    private $level = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600
    ];

    private $callHandlerMethod = [
        'Monolog\Handler\StreamHandler' => 'getStreamHandler',
        'Monolog\Handler\RotatingFileHandler' => 'getRotatingFileHandler',
        'Monolog\Handler\NativeMailHandler' => 'getNativeMailHandler',
        'Monolog\Handler\SwiftMailerHandler' => 'getSwiftMailerHandler',
        'JetFire\Framework\Log\PDOHandler' => 'getPDOHandler',
    ];

    private $setup = [];
    private $loggers = [];
    private $config;

    public function __construct($config){
        $this->config = $config;
    }

    public function setup($env){
        foreach($this->config[$env] as $id => $logger){
            if(isset($logger['handlers']))
                foreach($logger['handlers'] as $handler) {
                    $this->setupHandler($id, $handler);
                }
            if(isset($logger['processors']))
                foreach($logger['processors'] as $processor)
                    $this->setupProcessor($id,$processor);
        }
    }

    public function init(){
        foreach($this->setup as $logger => $params){
            $this->loggers[$logger] = new Logger($logger);
            foreach($params['handlers'] as $handler)
                $this->loggers[$logger]->pushHandler($handler);
            foreach($params['processors'] as $processor)
                $this->loggers[$logger]->pushProcessor($processor);
        }
    }

    public function getLogger($name){
        return $this->loggers[$name];
    }

    private function setupHandler($id,$handler){
        if(method_exists($this,$this->callHandlerMethod[$this->config['handlers'][$handler]['class']]))
            $this->setup[$id]['handlers'][$handler] = call_user_func_array([$this, $this->callHandlerMethod[$this->config['handlers'][$handler]['class']]], [$this->config['handlers'][$handler]]);
        else{
            $this->register($this->config['handlers'][$handler]['class'],[
                'shared' => true,
            ]);
            $this->setup[$id]['handlers'][$handler] = $this->get($this->config['handlers'][$handler]['class']);
        }
    }

    private function setupProcessor($id,$processor){
        $this->setup[$id]['processors'][$processor] = $this->get($this->config['processors'][$processor]['class']);
    }

    private function getFormatter($formatter = []){
        if(isset($formatter['params']))
            return $this->get($formatter['class'],$formatter['params']);
        return $this->get($formatter['class']);
    }

    private function getStreamHandler($params){
        $this->register($params['class'],[
            'shared' => true,
            'construct' => [$params['stream'],$this->level[$params['level']]]
        ]);
        return $this->get($params['class']);
    }

    private function getRotatingFileHandler($params){
        $this->register($params['class'],[
            'shared' => true,
            'construct' => [
                $params['stream'],
                isset($params['max_files'])?$params['max_files']:0,
                $this->level[$params['level']]
            ]
        ]);
        $handler = $this->get($params['class']);
        if(isset($params['formatter']))
            $handler->setFormatter($this->getFormatter($this->config['formatters'][$params['formatter']]));
        return $handler;
    }

    private function getNativeMailHandler($params){
        $this->register($params['class'],[
            'shared' => true,
            'construct' => [
                $params['to'],
                $params['subject'],
                $params['from'],
                $this->level[$params['level']]
            ]
        ]);
        return $this->get($params['class']);
    }

    private function getSwiftMailerHandler($params){
        $mail = $this->get('mail')->getMailer();
        if(!$mail instanceof SwiftMailer)
            throw new \Exception('Instance of JetFire\Mailer\SwiftMailer\SwiftMailer is required for getSwiftMailerHandler method');
        $this->register($params['class'], [
            'shared'    => true,
            'construct' => [
                $mail->getMailer(),
                $mail->getMail(),
                $this->level[$params['level']]
            ]
        ]);
        return $this->get($params['class']);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getPDOHandler($params){
        $db = $this->get('database');
        $config = $db->getParams();
        $params['table'] = isset($config['prefix'])?$config['prefix'].$params['table']:$params['table'];
        if(isset($db->getProviders()['pdo']))
            $pdo = $db->getProvider('pdo')->getOrm();
        else {
            $config = isset($config['orm']) ? $config['orm'] : $config;
            $pdo = new PDO($config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['db'], $config['user'], $config['pass']);
        }
        return $this->get($params['class'],[$pdo,$params['table'],$params['fields'],$this->level[$params['level']]]);
    }
} 