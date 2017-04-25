<?php

declare(strict_types = 1);

/**
 * Date: 05.09.16
 * Time: 14:51
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterFacade
 *
 * @package Rudra
 */
class RouterFacade implements RouterFacadeInterface
{

    use SetContainerTrait;

    /**
     * @var RouterFacade
     */
    protected $router;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine)
    {
        $this->container = $container;
        $this->router    = new Router($this->container, $namespace, $templateEngine);
        set_exception_handler([new RouterException(), 'handler']);
    }


    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route)
    {
        $this->router()->set($route);
    } // @codeCoverageIgnore

    /**
     * @param array $route
     * @param null  $params
     *
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void
    {
        $this->router()->directCall($route, $params);
    }

    /**
     * @param array $middleware
     */
    public function handleMiddleware(array $middleware)
    {
        $this->router()->handleMiddleware($middleware);
    }

    /**
     * @param string $method
     * @param array  $route
     *
     * @return mixed
     */
    public function middleware(string $method, array $route)
    {
        return $this->$method($route);
    }

    /**
     * @param array $route
     */
    public function get(array $route): void
    {
        $route['http_method'] = 'GET';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function post(array $route): void
    {
        $route['http_method'] = 'POST';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function put(array $route): void
    {
        $route['http_method'] = 'PUT';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function patch(array $route): void
    {
        $route['http_method'] = 'PATCH';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function delete(array $route): void
    {
        $route['http_method'] = 'DELETE';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function any(array $route): void
    {
        $route['http_method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->router()->set($route);
    }

    /**
     * @param array $route
     */
    public function resource(array $route): void
    {
        switch ($this->container()->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
                if ($this->container()->hasPost('_method')) {
                    $route = array_merge($route, $this->router()->setRequestMethod('REST'));
                } else {
                    $route['http_method'] = 'POST';
                    $route['method']      = 'create';
                }
                break;
            case 'PUT':
                $route['http_method'] = 'PUT';
                $route['method']      = 'update';
                break;
            case 'DELETE':
                $route['http_method'] = 'DELETE';
                $route['method']      = 'delete';
                break;
        }

        $this->router()->set($route);
    }

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
     */
    public function annotation(string $class, string $method, int $number = 0): void
    {
        $this->router()->matchAnnotation()->annotation($class, $method, $number);
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->router()->matchHttpMethod()->matchRequest()->isToken();
    }
}
