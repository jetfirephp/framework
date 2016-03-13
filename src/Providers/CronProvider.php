<?php

namespace JetFire\Framework\Providers;


use JetFire\Jobby\Jobby;
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
     * @param Jobby $jobby
     * @param array $cron
     */
    public function __construct(Jobby $jobby,$cron = []){
        $this->jobby = $jobby;
        foreach($cron as $name => $job){
            if(isset($job['controller']))
                $this->jobby['closure'] = $this->getClosure($job);
            elseif(isset($job['file']))
                $this->jobby['closure'] = function()use($job){require $job['file'];};
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
     * @return callable
     */
    private function getClosure($job){
        $collection = new RouteCollection();
        $collection->addRoutes([
            '/' => $job['controller']
        ],[
            'path' => ROOT.'/Views',
            'namespace' => '',
        ]);
        $router = new Router($collection);
        $router->setUrl('/');
        $router->match();
        return function()use($router){
            $router->callTarget();
            $router->response->sendContent();
        };
    }
} 