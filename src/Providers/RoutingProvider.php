<?php

namespace JetFire\Framework\Providers;

use JetFire\Routing\ResponseInterface;
use JetFire\Routing\RouteCollection;
use JetFire\Routing\Router;

/**
 * Class RoutingProvider
 * @package JetFire\Framework\Providers
 */
class RoutingProvider extends Provider
{

    /**
     * @var
     */
    protected $router;
    /**
     * @var RouteCollection
     */
    protected $collection;
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param RouteCollection $collection
     */
    public function __construct(RouteCollection $collection)
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
        return $this->response;
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
        $this->getApp()->data['template_extension'] = $extension = $template['engines'][$template['use']]['extension'];
        $this->response = $this->get($router['response']);
        $this->router = new Router($this->collection, $this->response);
        $ext = explode('.', $extension);
        $templateExtension = array_merge(['.html', '.php', '.json', '.xml'], ['.' . end($ext), $extension]);
        $this->router->setConfig([
            'matcher'            => $router['matcher'],
            'di'                 => function ($class) {
                $this->register($class, ['shared' => true]);
                return $this->get($class);
            },
            'templateExtension'  => $templateExtension,
            'generateRoutesPath' => $router['generateRoutePath']
        ]);
        $this->router->setResponses($responses);
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
                $ext => function ($route) use ($template) {
                    $this->register($template['view'], ['shared' => true]);
                    $view = $this->get($template['view']);
                    $view->setPath($route->getTarget('view_dir'));
                    $view->setExtension($template['engines'][$template['use']]['extension']);
                    $view->setTemplate(str_replace($route->getTarget('view_dir'), '', $route->getTarget('template')));
                    $view->addData(isset($route->getPath()['data']) ? $route->getPath()['data'] : []);
                    return $this->get('template')->getTemplate()->render($view);
                }
            ]
        ]);
    }

} 