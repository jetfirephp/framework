<?php

namespace JetFire\Framework\Providers;


class SessionProvider extends Provider{

    private $session;

    private $handlers = [
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler' => 'nativeHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler' => 'fileHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler' => 'pdoHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler' => 'memcacheHandler',
        'Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler' => 'memcachedHandler',
    ];

    private $storages = [
        'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage' => 'nativeStorage'
    ];

    public function __construct($config = [],$env){
        $handler = call_user_func_array([$this,$this->handlers[$config['handlers'][$config[$env]['handler']]['use']]],[$config['handlers'][$config[$env]['handler']]]);
        $storage =  call_user_func_array([$this,$this->storages[$config['storages'][$config[$env]['storage']]['use']]],[$config['storages'][$config[$env]['storage']],$handler]);
        $this->session = new $config[$env]['use']($storage);
    }

    public function start(){
        $this->session->start();
    }

    public function getSession(){
        return $this->session;
    }

    private function nativeStorage($config,$handler){
        if(isset($config['args']))
            return new $config['use']($config['args'],$handler);
        return new $config['use']([],$handler);
    }

    private function nativeHandler($config){
        return new $config['use'];
    }

    private function fileHandler($config){
        if(isset($config['args']) && isset($config['args'][0]))
            return new $config['use']($config['args'][0]);
        return new $config['use'];
    }

    private function pdoHandler($config){
        $db = $this->get('database');
        $params = $db->getParams();
        $config['args'][1]['db_table'] = isset($params['prefix'])?$params['prefix'].$config['args'][1]['db_table']:$config['args'][1]['db_table'];
        if(isset($db->getProviders()['pdo'])) {
            $pdo = $db->getProvider('pdo')->getOrm();
            $pdo->exec('CREATE TABLE IF NOT EXISTS '.$config['args'][1]['db_table'].' (
                    sess_id INT(6) UNSIGNED PRIMARY KEY,
                    sess_data VARCHAR(255) NOT NULL,
                    sess_lifetime DATETIME NOT NULL,
                    sess_time VARCHAR(100)
                )');
            return new $config['use']($pdo, $config['args'][1]);
        }
        return new $config['use']($config['args'][0],$config['args'][1]);
    }

    private function memcacheHandler($config){
        if(!isset($config['args']) || !isset($config['args'][0]) || !isset($config['args'][1]))
            throw new \InvalidArgumentException('Arguments expected for session memcache handler.');
        return new $config['use']($this->get($config['args'][0]),$config['args'][1]);
    }

    private function memcachedHandler($config){
        if(!isset($config['args']) || !isset($config['args'][0]) || !isset($config['args'][1]))
            throw new \InvalidArgumentException('Arguments expected for session memcached handler.');
        return new $config['use']($this->get($config['args'][0]),$config['args'][1]);
    }


} 