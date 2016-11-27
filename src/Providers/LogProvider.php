<?php

namespace JetFire\Framework\Providers;

use JetFire\Mailer\SwiftMailer\SwiftMailer;
use Monolog\Logger;
use PDO;

/**
 * Class LogProvider
 * @package JetFire\Framework\Providers
 */
class LogProvider extends Provider{

    /**
     * @var array
     */
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

    /**
     * @var array
     */
    private $callHandlerMethod = [
        'Monolog\Handler\StreamHandler' => 'getStreamHandler',
        'Monolog\Handler\RotatingFileHandler' => 'getRotatingFileHandler',
        'Monolog\Handler\NativeMailHandler' => 'getNativeMailHandler',
        'Monolog\Handler\SwiftMailerHandler' => 'getSwiftMailerHandler',
        'JetFire\Framework\Log\PDOHandler' => 'getPDOHandler',
        'Monolog\Handler\BrowserConsoleHandler' => 'getHandler',
    ];

    /**
     * @var array
     */
    private $setup = [];
    /**
     * @var array
     */
    private $loggers = [];
    /**
     * @var
     */
    private $config;

    /**
     * @param $config
     */
    public function __construct($config){
        $this->config = $config;
    }

    /**
     * @param $env
     */
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

    /**
     *
     */
    public function init(){
        foreach($this->setup as $logger => $params){
            $this->loggers[$logger] = new Logger($logger);
            if(isset($params['handlers']) && !empty($params['handlers']))
                foreach($params['handlers'] as $handler)
                    $this->loggers[$logger]->pushHandler($handler);
            if(isset($params['processors']) && !empty($params['processors']))
                foreach($params['processors'] as $processor)
                    $this->loggers[$logger]->pushProcessor($processor);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getLogger($name){
        return $this->loggers[$name];
    }

    /**
     * @param $id
     * @param $handler
     */
    private function setupHandler($id,$handler){
        $params = $this->config['handlers'][$handler];
        if(method_exists($this,$this->callHandlerMethod[$params['class']]))
            $this->setup[$id]['handlers'][$handler] = call_user_func_array([$this, $this->callHandlerMethod[$params['class']]], [$params]);
        else{
            $this->register($params['class'],[
                'shared' => true,
            ]);
            $this->setup[$id]['handlers'][$handler] = $this->get($params['class']);
        }
        if(isset($params['formatter']))
            $this->setup[$id]['handlers'][$handler]->setFormatter($this->getFormatter($this->config['formatters'][$params['formatter']]));
    }

    /**
     * @param $id
     * @param $processor
     */
    private function setupProcessor($id,$processor){
        $this->setup[$id]['processors'][$processor] = $this->get($this->config['processors'][$processor]['class']);
    }

    /**
     * @param array $formatter
     * @return mixed
     */
    private function getFormatter($formatter = []){
        if(isset($formatter['params']))
            return $this->get($formatter['class'],$formatter['params']);
        return $this->get($formatter['class']);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getStreamHandler($params){
        $this->register($params['class'],[
            'shared' => true,
            'construct' => [$params['stream'],$this->level[$params['level']]]
        ]);
        return $this->get($params['class']);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getRotatingFileHandler($params){
        $this->register($params['class'],[
            'shared' => true,
            'construct' => [
                $params['stream'],
                isset($params['max_files'])?$params['max_files']:0,
                $this->level[$params['level']]
            ]
        ]);
        return $this->get($params['class']);
    }

    /**
     * @param $params
     * @return mixed
     */
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

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * @param $params
     * @return mixed
     */
    private function getHandler($params){
        $this->register($params['class'],[
            'shared' => true,
        ]);
        return $this->get($params['class']);
    }
} 