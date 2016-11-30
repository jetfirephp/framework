<?php

namespace JetFire\Framework\Providers;


use InvalidArgumentException;
use JetFire\Framework\App;

/**
 * Class CacheProvider
 * @package JetFire\Framework\Providers
 */
class CacheProvider extends Provider{

    /**
     * @var array
     */
    private $config;
    /**
     * @var mixed
     */
    private $cache;
    /**
     * @var array
     */
    private $cacheDrivers = [
        'Doctrine\Common\Cache\ArrayCache' => 'getCache',
        'Doctrine\Common\Cache\ApcCache' => 'getCache',
        'Doctrine\Common\Cache\XcacheCache' => 'getCache',
        'Doctrine\Common\Cache\FilesystemCache' => 'getFileCache',
        'Doctrine\Common\Cache\MemcacheCache' => 'getMemcache',
        'Doctrine\Common\Cache\MemcachedCache' => 'getMemcached',
        'Doctrine\Common\Cache\RedisCache' => 'getRedis',
    ];

    /**
     * CacheProvider constructor.
     * @param array $config
     * @param $env
     */
    public function init($config = [], $env){
        $this->config = $config;
        $this->cache = call_user_func_array([$this,$this->cacheDrivers[$config['drivers'][$config[$env]]['class']]],[$config['drivers'][$config[$env]]]);
        $this->app->addAlias($config[$env],$config['drivers'][$config[$env]]['class']);
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getCache($key = null){
        if(is_null($key))
            return $this->cache;
        return call_user_func_array([$this,$this->cacheDrivers[$this->config['drivers'][$key]['class']]],[$this->config['drivers'][$key]]);
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getFileCache($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcache driver missing');
        $this->app->addRule($driver['class'],[
            'shared' => true,
            'construct' => [$driver['args'][0],$driver['args'][1]]
        ]);
        return $this->app->get($driver['class']);
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getMemcache($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcache driver missing');
        $this->app->addRule('Memcache',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->app->addRule($driver['class'],[
            'shared' => true,
            'call' => [
                'setMemcache' => [$this->app->get('Memcache')]
            ],
        ]);
        return $this->app->get($driver['class']);
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getMemcached($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcached driver missing');
        $this->app->addRule('Memcached',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->app->addRule($driver['class'],[
            'shared' => true,
            'call' => [
                'setMemcached' => [$this->app->get('Memcached')]
            ],
        ]);
        return $this->app->get($driver['class']);
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getRedis($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for redis driver missing');
        $this->app->addRule('Redis',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->app->addRule($driver['class'],[
            'shared' => true,
            'call' => [
                'setRedis' => [$this->app->get('Redis')]
            ],
        ]);
        return $this->app->get($driver['class']);
    }

} 