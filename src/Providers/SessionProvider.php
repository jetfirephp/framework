<?php

namespace JetFire\Framework\Providers;

/**
 * Class SessionProvider
 * @package JetFire\Framework\Providers
 */
class SessionProvider extends Provider
{

    /**
     * @var mixed
     */
    private $session;

    /**
     * @var array
     */
    private $handlers = [
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler' => 'nativeHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler' => 'fileHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler' => 'pdoHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler' => 'memcacheHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler' => 'memcachedHandler',
    ];

    /**
     * @var array
     */
    private $storages = [
        'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage' => 'nativeStorage',
        'Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage' => 'mockStorage',
        'Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage' => 'mockStorage'
    ];

    /**
     * @param array $config
     * @param $env
     */
    public function init($config = [], $env)
    {
        $handler = call_user_func_array([$this, $this->handlers[$config['handlers'][$config[$env]['handler']]['class']]], [$config['handlers'][$config[$env]['handler']]]);
        $storage = call_user_func_array([$this, $this->storages[$config['storages'][$config[$env]['storage']]['class']]], [$config['storages'][$config[$env]['storage']], $handler]);
        $this->session = new $config['class']($storage);
        $this->app->register($this->session);
    }

    /**
     *
     */
    public function start()
    {
        $this->session->start();
    }

    /**
     * @return \JetFire\Http\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param $config
     * @param $handler
     * @return mixed
     */
    private function nativeStorage($config, $handler)
    {
        if (isset($config['args']))
            return new $config['class']($config['args'], $handler);
        return new $config['class']([], $handler);
    }

    /**
     * @param $config
     * @param $handler
     * @return mixed
     */
    private function mockStorage($config, $handler){
        return new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    private function nativeHandler($config)
    {
        return new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    private function fileHandler($config)
    {
        if (isset($config['args']) && isset($config['args'][0]))
            return new $config['class']($config['args'][0]);
        return new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    private function pdoHandler($config)
    {
        $db = $this->app->get('database');
        $params = $db->getParams();
        $config['args'][1]['db_table'] = isset($params['prefix']) ? $params['prefix'] . $config['args'][1]['db_table'] : $config['args'][1]['db_table'];
        if (isset($db->getProviders()['pdo'])) {
            $pdo = $db->getProvider('pdo')->getOrm();
            $pdo->exec('CREATE TABLE IF NOT EXISTS ' . $config['args'][1]['db_table'] . ' (
                    sess_id INT(6) UNSIGNED PRIMARY KEY,
                    sess_data VARCHAR(255) NOT NULL,
                    sess_lifetime DATETIME NOT NULL,
                    sess_time VARCHAR(100)
                )');
            return new $config['class']($pdo, $config['args'][1]);
        }
        return new $config['class']($config['args'][0], $config['args'][1]);
    }

    /**
     * @param $config
     * @return mixed
     */
    private function memcacheHandler($config)
    {
        if (!isset($config['args']) || !isset($config['args'][0]) || !isset($config['args'][1]))
            throw new \InvalidArgumentException('Arguments expected for session memcache handler.');
        return new $config['class']($this->app->get($config['args'][0]), $config['args'][1]);
    }

    /**
     * @param $config
     * @return mixed
     */
    private function memcachedHandler($config)
    {
        if (!isset($config['args']) || !isset($config['args'][0]) || !isset($config['args'][1]))
            throw new \InvalidArgumentException('Arguments expected for session memcached handler.');
        return new $config['class']($this->app->get($config['args'][0]), $config['args'][1]);
    }


} 