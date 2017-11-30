<?php

namespace JetFire\Framework\Providers;

use InvalidArgumentException;

/**
 * Class CacheProvider
 * @package JetFire\Framework\Providers
 */
class CacheProvider extends Provider
{

    /**
     * @var array
     */
    protected $config;
    /**
     * @var mixed
     */
    protected $cache;
    /**
     * @var array
     */
    protected $cacheDrivers = [
        'Doctrine\Common\Cache\ArrayCache' => 'getNativeCache',
        'Doctrine\Common\Cache\ApcCache' => 'getNativeCache',
        'Doctrine\Common\Cache\XcacheCache' => 'getNativeCache',
        'Doctrine\Common\Cache\FilesystemCache' => 'getFileCache'
    ];

    /**
     * CacheProvider constructor.
     * @param array $config
     * @param $env
     */
    public function init($config = [], $env)
    {
        $this->config = $config;
        $this->cache = $this->getCache($config[$env]);
        $this->app->addAlias($config[$env], $config['drivers'][$config[$env]]['class']);
    }

    /**
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    public function getCache($key = null)
    {
        if (is_null($key)) {
            return $this->cache;
        }elseif (isset($this->cacheDrivers[$this->config['drivers'][$key]['class']])) {
            return call_user_func_array([$this, $this->cacheDrivers[$this->config['drivers'][$key]['class']]], [$this->config['drivers'][$key]]);
        } elseif (isset($this->config['drivers'][$key]['callback'])) {
            $callback = $this->config['drivers'][$key]['callback'];
            if (!$this->app->has($callback)) {
                throw new \Exception('Callback : ' . $callback . ' not found in DI.');
            }
            if (!method_exists(($provider = $this->app->get($callback)), 'getCache')) {
                throw new \Exception('Method "getCache" not found in ' . get_class($provider));
            }
            return $provider->getCache($this->config['drivers'][$key]);
        }
        return null;
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getNativeCache($driver)
    {
        $this->app->addRule($driver['class'], [
            'shared' => true
        ]);
        return $this->app->get($driver['class']);
    }

    /**
     * @param $driver
     * @return mixed
     */
    private function getFileCache($driver)
    {
        if (!isset($driver['args'][0]) || !isset($driver['args'][1])) {
            throw new InvalidArgumentException('Arguments for file cache driver missing');
        }
        $this->app->addRule($driver['class'], [
            'shared' => true,
            'construct' => [$driver['args'][0], $driver['args'][1]]
        ]);
        return $this->app->get($driver['class']);
    }

} 