<?php

namespace JetFire\Framework\Providers;

use JetFire\Db\Pdo\PdoModel;
use JetFire\Mailer\SwiftMailer\SwiftMailer;
use Monolog\Logger;
use PDO;

/**
 * Class LogProvider
 * @package JetFire\Framework\Providers
 */
class LogProvider extends Provider
{

    /**
     * @var array
     */
    protected $level = [
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
    protected $callHandlerMethod = [
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
    protected $setup = [];
    /**
     * @var array
     */
    protected $loggers = [];
    /**
     * @var array
     */
    protected $default = [];
    /**
     * @var
     */
    protected $config;

    /**
     * @param $config
     * @param $use
     * @param array $default
     */
    public function init($config, $use, $default = [])
    {
        $this->config = $config;
        $this->default = $default;
        foreach ($use as $id => $logger) {
            if (isset($logger['handlers'])) {
                foreach ($logger['handlers'] as $handler) {
                    $this->setupHandler($id, $handler);
                }
            }
            if (isset($logger['processors'])) {
                foreach ($logger['processors'] as $processor) {
                    $this->setupProcessor($id, $processor);
                }
            }
        }
    }

    /**
     *
     */
    public function setup()
    {
        foreach ($this->setup as $logger => $params) {
            $this->loggers[$logger] = new Logger($logger);
            if (isset($params['handlers']) && !empty($params['handlers'])) {
                foreach ($params['handlers'] as $handler) {
                    $this->loggers[$logger]->pushHandler($handler);
                }
            }
            if (isset($params['processors']) && !empty($params['processors'])) {
                foreach ($params['processors'] as $processor) {
                    $this->loggers[$logger]->pushProcessor($processor);
                }
            }
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getLogger($name)
    {
        return $this->loggers[$name];
    }

    /**
     * @param $id
     * @param $handler
     */
    private function setupHandler($id, $handler)
    {
        $params = $this->config['handlers'][$handler];
        if (method_exists($this, $this->callHandlerMethod[$params['class']])) {
            $this->setup[$id]['handlers'][$handler] = call_user_func_array([$this, $this->callHandlerMethod[$params['class']]], [$params]);
        } else {
            $this->app->addRule($params['class'], [
                'shared' => true,
            ]);
            $this->setup[$id]['handlers'][$handler] = $this->app->get($params['class']);
        }
        if (isset($params['formatter'])) {
            $this->setup[$id]['handlers'][$handler]->setFormatter($this->getFormatter($this->config['formatters'][$params['formatter']]));
        }
    }

    /**
     * @param $id
     * @param $processor
     */
    private function setupProcessor($id, $processor)
    {
        $this->setup[$id]['processors'][$processor] = $this->app->get($this->config['processors'][$processor]['class']);
    }

    /**
     * @param array $formatter
     * @return mixed
     */
    private function getFormatter($formatter = [])
    {
        return (isset($formatter['params']))
            ? $this->app->get($formatter['class'], $formatter['params'])
            : $this->app->get($formatter['class']);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getStreamHandler($params)
    {
        return new $params['class']($params['stream'], $this->level[$params['level']]);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getRotatingFileHandler($params)
    {
        $max = isset($params['max_files']) ? $params['max_files'] : 0;
        return new $params['class']($params['stream'], $max, $this->level[$params['level']]);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getNativeMailHandler($params)
    {
        return new $params['class']($params['to'], $params['subject'], $params['from'], $this->level[$params['level']]);
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    private function getSwiftMailerHandler($params)
    {
        $mail = $this->app->get('mail')->getMailer();
        if (!$mail instanceof SwiftMailer) {
            throw new \Exception('Instance of JetFire\Mailer\SwiftMailer\SwiftMailer is required for getSwiftMailerHandler method');
        }
        return new $params['class']($mail->getMailer(), $mail->getMail(), $this->level[$params['level']]);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getPDOHandler($params)
    {
        $db = $this->app->get('database');
        $config = $db->getParams();
        $params['table'] = (isset($config[$this->default['db']]) && isset($config[$this->default['db']]['prefix'])) ? $config[$this->default['db']]['prefix'] . $params['table'] : $params['table'];
        $pdo = null;
        if (isset($db->getProviders()['pdo']) && isset($config['default'])) {
            /** @var PdoModel $orm */
            $orm = $db->getProvider('pdo');
            $orm->setDb('default');
            $pdo = $orm->getOrm();
        }elseif(is_null($pdo)) {
            if(isset($config[$this->default['db']])) {
                $pdo = new PDO($config[$this->default['db']]['driver'] . ':host=' . $config[$this->default['db']]['host'] . ';dbname=' . $config[$this->default['db']]['db'], $config[$this->default['db']]['user'], $config[$this->default['db']]['pass']);
            }
        }
        return $this->app->get($params['class'], [$pdo, $params['table'], $params['fields'], $this->level[$params['level']]]);
    }

    /**
     * @param $params
     * @return mixed
     */
    private function getHandler($params)
    {
        return new $params['class']();
    }
} 