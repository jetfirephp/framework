<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\System\Controller;
use JetFire\Framework\System\View;

/**
 * Class EventProvider
 * @package JetFire\Framework\Providers
 */
class EventProvider extends Provider
{

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param array $options
     */
    public function setAsyncOptions($options = [])
    {
        $this->options = $options;
    }

    /**
     * @param $event
     * @param $value
     */
    public function emit($event, $value)
    {
        $data = $this->app->data;
        if (isset($data['app']['events']) && isset($data['app']['events'][$event])) {
            $event = $data['app']['events'][$event];
            if (!is_array($event)) $event = [$event];
            if (!is_array($value)) $value = [$value];
            /** @var Controller $controller */
            $controller = $this->app->get('JetFire\Framework\System\Controller');
            foreach ($event as $callbacks) {
                if ($callbacks['type'] == 'linear' && isset($callbacks['callback']))
                    $this->callSynchronous($controller, $callbacks, $value);
                elseif ($callbacks['type'] == 'async' && isset($callbacks['route']))
                    $this->callAsynchronous($callbacks, $value);
            }
        }
    }

    /**
     * @param Controller $controller
     * @param $callbacks
     * @param $value
     */
    private function callSynchronous(Controller $controller, $callbacks, $value)
    {
        $callback = explode('@', $callbacks['callback']);
        if (class_exists($callback[0]) && method_exists($callback[0], $callback[1]))
            $controller->callMethod($callback[0], $callback[1], $value);
    }

    /**
     * @param $callbacks
     * @param $value
     */
    private function callAsynchronous($callbacks, $value)
    {
        /** @var View $view */
        $view = $this->app->get('response')->getView();
        $method = (isset($callbacks['method'])) ? strtoupper($callbacks['method']) : 'GET';
        $args = (isset($callbacks['args'])) ? $callbacks['args'] : [];
        $path = $view->path($callbacks['route'], $args);
        $request = $this->app->get('JetFire\Framework\System\RequestAsync');

        ($method == 'GET')
            ? $request->get($path, $value, $this->options)
            : $request->post($path, $value, $this->options);
    }

} 