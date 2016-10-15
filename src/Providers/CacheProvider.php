<?php

namespace JetFire\Framework\Providers;


use InvalidArgumentException;

class CacheProvider extends Provider{

    private $config;
    private $cache;
    private $cacheDrivers = [
        'Doctrine\Common\Cache\ArrayCache' => 'getCache',
        'Doctrine\Common\Cache\ApcCache' => 'getCache',
        'Doctrine\Common\Cache\XcacheCache' => 'getCache',
        'Doctrine\Common\Cache\FilesystemCache' => 'getFileCache',
        'Doctrine\Common\Cache\MemcacheCache' => 'getMemcache',
        'Doctrine\Common\Cache\MemcachedCache' => 'getMemcached',
        'Doctrine\Common\Cache\RedisCache' => 'getRedis',
    ];

    public function __construct($config = [],$env){
        $this->config = $config;
        $this->cache = call_user_func_array([$this,$this->cacheDrivers[$config['drivers'][$config[$env]]['class']]],[$config['drivers'][$config[$env]]]);
        $this->addAlias($config[$env],$config['drivers'][$config[$env]]['class']);
    }

    public function getCache($key = null){
        if(is_null($key))
            return $this->cache;
        return call_user_func_array([$this,$this->cacheDrivers[$this->config['drivers'][$key]['class']]],[$this->config['drivers'][$key]]);
    }

    private function getFileCache($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcache driver missing');
        $this->register($driver['class'],[
            'shared' => true,
            'construct' => [$driver['args'][0],$driver['args'][1]]
        ]);
        return $this->get($driver['class']);
    }

    private function getMemcache($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcache driver missing');
        $this->register('Memcache',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->register($driver['class'],[
            'shared' => true,
            'call' => [
                'setMemcache' => [$this->get('Memcache')]
            ],
        ]);
        return $this->get($driver['class']);
    }

    private function getMemcached($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcached driver missing');
        $this->register('Memcached',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->register($driver['class'],[
            'shared' => true,
            'call' => [
                'setMemcached' => [$this->get('Memcached')]
            ],
        ]);
        return $this->get($driver['class']);
    }

    private function getRedis($driver){
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for redis driver missing');
        $this->register('Redis',[
            'shared' => true ,
            'call' => [
                'connect' => [$driver['args'][0],$driver['args'][1]]
            ]
        ]);
        $this->register($driver['class'],[
            'shared' => true,
            'call' => [
                'setRedis' => [$this->get('Redis')]
            ],
        ]);
        return $this->get($driver['class']);
    }

} 