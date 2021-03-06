<?php

namespace JetFire\Framework\Providers;

use Doctrine\DBAL\Logging\DebugStack;
use JetFire\Db\Doctrine\DoctrineConstructor;
use JetFire\Db\Model;
use JetFire\Db\Pdo\PdoConstructor;
use JetFire\Db\RedBean\RedBeanConstructor;
use JetFire\Framework\System\Controller;

/**
 * Class DbProvider
 * @package JetFire\Framework\Providers
 */
class DbProvider extends Provider
{

    /**
     * @var array
     */
    protected $providers = [];
    /**
     * @var
     */
    protected $db;
    /**
     * @var string
     */
    protected $env;
    /**
     * @var
     */
    protected $ormCollection;

    /**
     * @param array $ormCollection
     * @param $default_db
     * @param array $db
     * @param $blocks
     * @param string $env
     */
    public function init($ormCollection, $default_db, $db, $blocks, $env)
    {
        $this->ormCollection = $ormCollection;
        $this->env = $env;
        $params = [];
        foreach ($blocks as $block) {
            $path = rtrim($block['path'], '/');
            $block_path = $path . '/Models/';
            if(isset($block['model'])){
                if(is_array($block['model']) && isset($block['model'][$default_db])){
                    $path = rtrim($block['model'][$default_db], '/');
                    $block_path = $path;
                }elseif(is_string($block['model'])) {
                    $path = rtrim($block['model'], '/');
                    $block_path = $path;
                }else{
                    continue;
                }
            }

            if (is_dir($block_path)) {
                $params['path'][] = $block_path;
            }
            $params['repositories'][] = (isset($block['repositories']))
                ? ['path' => $block['repositories']['path'], 'namespace' => $block['repositories']['namespace']]
                : ['path' => $path, 'namespace' => $block['namespace'] . '\Models'];
        }
        if ($env == 'dev') $params['dev'] = true;
        foreach ($db as $key => $uniqueDb) $db[$key] = array_merge($db[$key], $params);
        $this->db = $db;
        foreach ($ormCollection['use'] as $key)
            $this->providers[$key] = function () use ($key, $ormCollection) {
                $orm = (is_array($ormCollection['drivers'][$key])) ? $ormCollection['drivers'][$key]['class'] : $ormCollection['drivers'][$key];
                $options = $this->setConfiguration($key);
                $this->app->addRule($orm, [
                    'shared' => true,
                    'construct' => [$this->db, $options]
                ]);
                return $this->app->get($orm);
            };

    }

    /**
     * @param $orm
     * @return array
     */
    public function setConfiguration($orm)
    {
        switch ($orm) {
            case 'doctrine':
                return [
                    'cache' => ($this->env == 'prod') ? $this->app->get('cache')->getCache($this->ormCollection['drivers']['doctrine']['cache']) : null,
                    'functions' => $this->ormCollection['drivers']['doctrine']['functions'],
                    'events' => $this->getDoctrineEvents($this->ormCollection['drivers']['doctrine']['events']),
                ];
                break;
        }
        return [];
    }

    /**
     * @param array $events
     * @return array
     */
    private function getDoctrineEvents($events = []){
        /** @var Controller $controller */
        $controller = $this->app->get('JetFire\Framework\System\Controller');
        if(isset($events['listeners']) && is_array($events['listeners'])) {
            foreach ($events['listeners'] as $key => $listener) {
                if (is_array($listener) && isset($listener[1])) {
                    $events['listeners'][$key][1] = $controller->callController($listener);
                }
            }
        }
        if(isset($events['subscribers']) && is_array($events['subscribers'])) {
            foreach ($events['subscribers'] as $key => $subscriber) {
                $events['subscribers'][$key] = $controller->callController($subscriber);
            }
        }
        return $events;
    }

    /**
     * @param array $default
     */
    public function provide($default = [])
    {
        Model::provide($this->providers, $default);
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param $key
     * @return DoctrineConstructor | RedBeanConstructor | PdoConstructor
     */
    public function getProvider($key)
    {
        return call_user_func($this->providers[$key]);
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getParams($key = null)
    {
        return (isset($this->db[$key]))
            ? $this->db[$key]
            : $this->db;
    }

    /**
     * @param bool $enable
     */
    public function setDebugBar($enable = true)
    {
        if ($this->env == 'dev' && $enable) {
            $debugStack = new DebugStack();
            Model::orm('doctrine')->getOrm()->getConnection()->getConfiguration()->setSQLLogger($debugStack);
            $this->app->get('debug_toolbar')->loadDoctrineDebugger($debugStack);
        }
    }

} 