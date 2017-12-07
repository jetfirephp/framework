<?php

namespace JetFire\Framework\Providers;

use JetFire\Routing\Matcher\MatcherInterface;
use JetFire\Routing\MiddlewareInterface;
use JetFire\Routing\Route;
use JetFire\Routing\RouteCollection;
use JetFire\Routing\Router;

/**
 * Class RoutingProvider
 * @package JetFire\Framework\Providers
 */
class RoutingProvider extends Provider
{

    /**
     * @var Router
     */
    protected $router;
    /**
     * @var RouteCollection
     */
    protected $collection;
    /**
     * @var MiddlewareInterface
     */
    protected $middleware;

    /**
     * @param RouteCollection $collection
     */
    public function init(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->router->response;
    }

    /**
     * @param $routes
     */
    public function setRoutes($routes)
    {
        foreach ($routes as $key => $block) {
            if (is_array($block)) {
                $route = isset($block['route']) ? $block['route'] : [];
                $block['view_dir'] = isset($block['view_dir']) ? $block['view_dir'] : rtrim($block['path'], '/') . '/Views';
                $options = isset($block['prefix'])
                    ? ['block' => $block['path'], 'view_dir' => $block['view_dir'], 'ctrl_namespace' => $block['namespace'] . '\Controllers', 'prefix' => $block['prefix']]
                    : ['block' => $block['path'], 'view_dir' => $block['view_dir'], 'ctrl_namespace' => $block['namespace'] . '\Controllers'];
                if (isset($block['subdomain'])) $options['subdomain'] = $block['subdomain'];
                if (isset($block['protocol'])) $options['protocol'] = $block['protocol'];
                if (isset($block['params'])) $options['params'] = $block['params'];
                $this->collection->addRoutes($route, $options);
            }
        }
    }

    /**
     * @param $router
     * @param $template
     */
    public function setRouter($router, $template)
    {
        $this->app->data['template_extension'] = $extension = $template['engines'][$template['use']]['extension'];
        $response = $this->app->get($router['response']);
        $this->router = new Router($this->collection, $response);
        $this->app->register($this->router);
        $this->setResolver($router);
        $ext = explode('.', $extension);
        $templateExtension = array_merge(['.html', '.php', '.json', '.xml'], ['.' . end($ext), $extension]);
        $this->router->setConfig([
            'di' => function ($class) {
                $this->app->addRule($class, ['shared' => true]);
                return $this->app->get($class);
            },
            'templateExtension' => $templateExtension,
            'generateRoutesPath' => $router['generateRoutePath']
        ]);
    }

    /**
     * @param $class
     * @param array $rules
     */
    public function setMiddleware($class, $rules = [])
    {
        $this->middleware = new $class($this->router);
        foreach ($rules as $action => $rule){
            $this->middleware->setCallbackAction($action, $rule);
        }
        $this->router->addMiddleware($this->middleware);
    }

    /**
     * @param $router
     */
    private function setResolver($router)
    {
        if (!is_array($router['use'])) $router['use'] = [$router['use']];
        foreach ($router['use'] as $matcher) {
            /** @var MatcherInterface $class */
            $class = new $router['matcher'][$matcher]['class']($this->router);
            $class->setResolver($router['matcher'][$matcher]['resolver']);
            $this->router->addMatcher($class);
        }
    }

    /**
     * @param $template
     */
    public function setTemplateCallback($template)
    {
        $ext = explode('.', $template['engines'][$template['use']]['extension']);
        $ext = end($ext);
        $this->router->setConfig([
            'templateCallback' => [
                $ext => function (Route $route) use ($template) {
                    $this->app->addRule($template['view'], ['shared' => true]);
                    $view = $this->app->get($template['view']);
                    $dir = (empty($route->getTarget('view_dir')))
                        ? substr($route->getTarget('template'), 0, strrpos($route->getTarget('template'), '/'))
                        : $route->getTarget('view_dir');
                    $view->setPath($dir);
                    $view->setExtension($template['engines'][$template['use']]['extension']);
                    $view->setTemplate(str_replace($dir, '', $route->getTarget('template')));
                    $data = (isset($route->getTarget()['data'])) ? $route->getTarget('data') : [];
                    $flash = $this->app->get('session')->getSession()->allFlash();
                    foreach ($flash as $key => $content)
                        $data[$key] = $content;
                    $view->addData(isset($route->getParams()['data']) ? array_merge($route->getParams()['data'], $data) : $data);
                    return $this->app->get('template')->getTemplate()->render($view);
                }
            ]
        ]);
    }

} 