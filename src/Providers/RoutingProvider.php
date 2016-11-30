<?php

namespace JetFire\Framework\Providers;

use JetFire\Framework\App;
use JetFire\Routing\Matcher\MatcherInterface;
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
     * @param App $app
     * @param RouteCollection $collection
     */
    public function __construct(App $app, RouteCollection $collection)
    {
        parent::__construct($app);
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
     * @param array $middleware
     */
    public function setRoutes($routes, $middleware = [])
    {
        if (!empty($middleware) && is_array($middleware))
            $this->collection->setMiddleware($middleware);
        foreach ($routes as $key => $block) {
            if (is_array($block)) {
                $path = (substr($block['path'], -4) == '.php') ? $block['path'] : rtrim($block['path'], '/') . '/routes.php';
                $block['view_dir'] = isset($block['view_dir']) ? $block['view_dir'] : rtrim($block['path'], '/') . '/Views';
                $options = isset($block['prefix'])
                    ? ['block' => $block['path'], 'view_dir' => $block['view_dir'], 'ctrl_namespace' => $block['namespace'] . '\Controllers', 'prefix' => $block['prefix']]
                    : ['block' => $block['path'], 'view_dir' => $block['view_dir'], 'ctrl_namespace' => $block['namespace'] . '\Controllers'];
                if (isset($block['subdomain'])) $options['subdomain'] = $block['subdomain'];
                $this->collection->addRoutes($path, $options);
            }
        }
    }

    /**
     * @param $router
     * @param $template
     * @param $responses
     */
    public function setRouter($router, $template, $responses)
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
        $this->router->setResponses($responses);
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
                    $dir = ($route->getTarget('view_dir') == '')
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