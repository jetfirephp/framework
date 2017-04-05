<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\System\View;
use Jobby\Jobby;
use JetFire\Routing\RouteCollection;
use JetFire\Routing\Router;

/**
 * Class CronProvider
 * @package JetFire\Framework\Providers
 */
class CronProvider extends Provider
{

    /**
     * @var Jobby
     */
    protected $jobby;

    /**
     * @param RouteCollection $collection
     * @param View $view
     * @param Jobby $jobby
     * @param array $cron
     */
    public function init(RouteCollection $collection, View $view, Jobby $jobby, $cron = [])
    {
        $this->jobby = $jobby;
        foreach ($cron as $name => $job) {
            if (isset($job['controller']))
                $job['closure'] = $this->getClosure($job, $collection);
            elseif (isset($job['file']))
                $job['closure'] = function () use ($job) {
                    require $job['file'];
                };
            elseif (isset($job['route'])) {
                $route = array_merge(['name' => '', 'arguments' => [], 'subdomain' => ''], $job['route']);
                $path = $view->path($route['name'], $route['arguments'], $route['subdomain']);
                $job['closure'] = function () use ($path) {
                    echo file_get_contents($path);
                    return true;
                };;
            }
            if (!isset($job['output']))
                $job['output'] = ROOT . '/storage/cron/command.log';
            $this->jobby->add($name, $job);
        }
    }

    /**
     *
     */
    public function run()
    {
        $this->jobby->run();
    }

    /**
     * @param $job
     * @param $collection
     * @return callable
     */
    private function getClosure($job, RouteCollection $collection)
    {
        $collection->addRoutes([
            '/' => [
                'use' => $job['controller'],
                'ajax' => (isset($job['ajax']) && $job['ajax']) ? true : false
            ]
        ], [
            'path' => ROOT . '/Views',
            'namespace' => '',
        ]);
        $router = new Router($collection);
        $router->setUrl('/');
        $router->match();
        return function () use ($router) {
            $router->callTarget();
            $router->response->sendContent();
        };
    }

} 