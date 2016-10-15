<?php

namespace JetFire\Framework\Providers;

use JetFire\Db\Doctrine\DoctrineConstructor;
use JetFire\Db\Model;
use JetFire\Db\Pdo\PdoConstructor;
use JetFire\Db\RedBean\RedBeanConstructor;

/**
 * Class DbProvider
 * @package JetFire\Framework\Providers
 */
class DbProvider extends Provider{

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var
     */
    private $db;

    /**
     * @var
     */
    private $ormCollection;

    /**
     * @param array $ormCollection
     * @param array $db
     * @param $blocks
     * @param string $env
     */
    public function __construct($ormCollection,$db,$blocks,$env){
        $this->ormCollection = $ormCollection;
        $params = [];
        foreach($blocks as $block){
            $block_path = (isset($block['model']))
                ? ($path = rtrim($block['model'],'/'))
                : ($path = rtrim($block['path'],'/').'/Models/');
            if(is_dir($block_path)) $params['path'][] = $block_path;
            $params['repositories'][] = (isset($block['repositories']))
                ? ['path' => $block['repositories']['path'], 'namespace' => $block['repositories']['namespace']]
                : ['path' => $path, 'namespace' => $block['namespace'].'\Models'];
        }
        if($env == 'dev') $params['dev'] = true;
        foreach($db as $key => $uniqueDb) $db[$key] = array_merge($db[$key],$params);
        $this->db = $db;
        foreach ($ormCollection['use'] as $key)
            $this->providers[$key] =  function()use($key,$ormCollection){
                $orm = (is_array($ormCollection['drivers'][$key]))?$ormCollection['drivers'][$key]['class']:$ormCollection['drivers'][$key];
                $options = $this->setConfiguration($key);
                $this->register($orm,[
                    'shared' => true,
                    'construct' => [array_merge($this->db,$options)]
                ]);
                return $this->get($orm);
            };

    }

    public function setConfiguration($orm){
        switch($orm){
            case 'doctrine':
                return [
                    'cache' => $this->get('cache')->getCache($this->ormCollection['drivers']['doctrine']['cache']),
                    'functions' => $this->ormCollection['drivers']['doctrine']['functions']
                ];
                break;
        }
        return [];
    }


    /**
     * @param array $default
     */
    public function provide($default = []){

        Model::provide($this->providers,$default);
    }

    /**
     * @return array
     */
    public function getProviders(){
        return $this->providers;
    }

    /**
     * @param $key
     * @return DoctrineConstructor | RedBeanConstructor | PdoConstructor
     */
    public function getProvider($key){
        return call_user_func($this->providers[$key]);
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getParams($key = null){
        return (isset($this->db[$key]))
            ? $this->db[$key]
            : $this->db;
    }

} 