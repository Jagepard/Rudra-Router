<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Traits;

/**
 * Class RouterMethodTrait
 * @package Rudra
 */
trait RouterMethodTrait
{

    /**
     * @param string $method
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     * @return mixed
     */
    public function middleware(string $method, string $pattern, $target, array $middleware)
    {
        $this->setRoute($pattern, $target, strtoupper($method), $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function get(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'GET');
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function post(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'POST');
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function put(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'PUT');
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function patch(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'PATCH');
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function delete(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'DELETE');
    }

    /**
     * @param string $pattern
     * @param        $target
     */
    public function any(string $pattern, $target): void
    {
        $this->setRoute($pattern, $target, 'GET|POST|PUT|PATCH|DELETE');
    }

    /**
     * @param string $pattern
     * @param string $controller
     * @param array  $actions
     */
    public function resource(string $pattern, string $controller, array $actions = ['read', 'create', 'update', 'delete']): void
    {
        $route['pattern']    = $pattern;
        $route['controller'] = $controller;

        switch ($this->container->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = $actions[0];
                break;
            case 'POST':
                if ($this->container->hasPost('_method')) {
                    $route = array_merge($route, $this->setRequestMethod('REST'));
                    break;
                }
                $route['http_method'] = 'POST';
                $route['method']      = $actions[1];
                break;
            case 'PUT':
                $route['http_method'] = 'PUT';
                $route['method']      = $actions[2];
                break;
            case 'DELETE':
                $route['http_method'] = 'DELETE';
                $route['method']      = $actions[3];
                break;
        }

        $this->set($route);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param string $httpMethod
     * @param array  $middleware
     */
    protected function setRoute(string $pattern, $target, string $httpMethod, array $middleware = []): void
    {
        $route['http_method'] = $httpMethod;
        $route['pattern']     = $pattern;

        if (count($middleware)) {
            if (array_key_exists('before', $middleware)) {
                $route['middleware'] = $middleware['before'];
            }

            if (array_key_exists('after', $middleware)) {
                $route['after_middleware'] = $middleware['after'];
            }
        }

        (is_callable($target))
            ? $route['method'] = $target
            : list($route['controller'], $route['method']) = explode('::', $target);

        $this->set($route);
    }

    /**
     * @param string|null $param
     * @return mixed
     */
    abstract protected function setRequestMethod(string $param = null);

    /**
     * @param array $route
     * @return mixed
     */
    abstract public function set(array $route);
}
