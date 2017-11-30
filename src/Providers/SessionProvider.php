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
    protected $session;

    /**
     * @var array
     */
    protected $handlers = [
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler' => 'nativeHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler' => 'fileHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler' => 'pdoHandler'
    ];

    /**
     * @var array
     */
    protected $storages = [
        'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage' => 'nativeStorage',
        'Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage' => 'mockStorage',
        'Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage' => 'mockStorage'
    ];

    /**
     * @param array $config
     * @param $env
     * @throws \Exception
     */
    public function init($config = [], $env)
    {
        $handler = null;
        if (isset($this->handlers[$config['handlers'][$config[$env]['handler']]['class']])) {
            $handler = call_user_func_array([$this, $this->handlers[$config['handlers'][$config[$env]['handler']]['class']]], [$config['handlers'][$config[$env]['handler']]]);
        } elseif (isset($config['handlers'][$config[$env]['handler']]['callback'])) {
            $callback = $config['handlers'][$config[$env]['handler']]['callback'];
            if (!$this->app->has($callback)) {
                throw new \Exception('Callback : ' . $callback . ' not found in DI.');
            }
            if (!method_exists(($provider = $this->app->get($callback)), 'getHandler')) {
                throw new \Exception('Method "getHandler" not found in ' . get_class($provider));
            }
            $handler = $provider->getHandler($config['handlers'][$config[$env]['handler']]);
        }
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
    protected function nativeStorage($config, $handler)
    {
        return (isset($config['args']))
            ? new $config['class']($config['args'], $handler)
            : new $config['class']([], $handler);
    }

    /**
     * @param $config
     * @param $handler
     * @return mixed
     */
    protected function mockStorage($config, $handler)
    {
        return new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    protected function nativeHandler($config)
    {
        return new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    protected function fileHandler($config)
    {
        return (isset($config['args']) && isset($config['args'][0]))
            ? new $config['class']($config['args'][0])
            : new $config['class'];
    }

    /**
     * @param $config
     * @return mixed
     */
    protected function pdoHandler($config)
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

} 