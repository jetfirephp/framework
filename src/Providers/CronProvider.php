<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\System\Controller;
use JetFire\Framework\System\View;
use Jobby\Jobby;

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
     * @var View
     */
    protected $view;

    /**
     * @var array
     */
    protected $stream = [
        'http' => [
            'method' => 'GET'
        ]
    ];

    /**
     * @param View $view
     * @param Jobby $jobby
     */
    public function init(View $view, Jobby $jobby)
    {
        $this->jobby = $jobby;
        $this->view = $view;
    }

    /**
     * @param array $cron
     */
    public function setCron($cron = [])
    {
        foreach ($cron as $name => $job) {
            if (isset($job['controller'])) {
                $job['closure'] = $this->callController($job);
            } elseif (isset($job['file'])) {
                $job['closure'] = function () use ($job) {
                    require $job['file'];
                };
            } elseif (isset($job['route'])) {
                $job['closure'] = $this->callRoute($job);
            }
            if (!isset($job['output']))
                $job['output'] = ROOT . '/storage/cron/command.log';
            $this->jobby->add($name, $job);
        }
    }

    /**
     * @param array $stream
     */
    public function setStream($stream = [])
    {
        $this->stream = array_merge_recursive($this->stream, $stream);
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
     * @return \Closure
     */
    private function callController($job)
    {
        /** @var Controller $controller */
        $controller = $this->app->get('JetFire\Framework\System\Controller');
        $callback = explode('@', $job['controller']);
        if (isset($callback[1])) {
            $args = isset($job['args']) ? $job['args'] : [];
            return function () use ($controller, $callback, $args) {
                $controller->callMethod($callback[0], $callback[1], $args);
            };
        }
        return function () {};
    }

    /**
     * @param $job
     * @return \Closure
     */
    private function callRoute($job)
    {
        $route = array_merge(['name' => '', 'arguments' => [], 'subdomain' => ''], $job['route']);
        $path = $this->view->path($route['name'], $route['arguments'], $route['subdomain']);
        $stream = $this->stream;
        return function () use ($path, $stream) {
            $context = stream_context_create($stream);
            file_get_contents($path, false, $context);
            return true;
        };
    }
} 