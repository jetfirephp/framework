<?php

namespace JetFire\Framework\Providers;

use Jobby\Jobby;
use JetFire\Routing\RouteCollection;
use JetFire\Routing\Router;

/**
 * Class CronProvider
 * @package JetFire\Framework\Providers
 */
class CronProvider extends Provider{

    /**
     * @var Jobby
     */
    private $jobby;

    /**
     * @param RouteCollection $collection
     * @param Jobby $jobby
     * @param array $cron
     * @throws \Jobby\Exception
     */
    public function init(RouteCollection $collection, Jobby $jobby, $cron = []){
        $this->jobby = $jobby;
        foreach($cron as $name => $job){
            if(isset($job['controller']))
                $job['closure'] = $this->getClosure($job, $collection);
            elseif(isset($job['file']))
                $job['closure'] = function()use($job){require $job['file'];};
            if(!isset($job['output']))
                $job['output'] = ROOT.'/storage/cron/command.log';
            $this->jobby->add($name,$job);
        }
    }

    /**
     *
     */
    public function run(){
        $this->jobby->run();
    }

    /**
     * @param $job
     * @param $collection
     * @return callable
     */
    private function getClosure($job, RouteCollection $collection){
        $collection->addRoutes([
            '/' => [
                'use' => $job['controller'],
                'ajax' => (isset($job['ajax']) && $job['ajax'])?true:false
            ]
        ],[
            'path' => ROOT.'/Views',
            'namespace' => '',
        ]);
        $router = new Router($collection);
        $router->setUrl('/');
        $router->match();
        return function() use ($router){
            $router->callTarget();
            $router->response->sendContent();
        };
    }
} 