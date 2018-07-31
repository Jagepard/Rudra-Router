<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
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
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function get(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET', $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function post(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'POST', $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function put(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PUT', $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function patch(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PATCH', $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function delete(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'DELETE', $middleware);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param array  $middleware
     */
    public function any(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET|POST|PUT|PATCH|DELETE', $middleware);
    }

    /**
     * @param string $pattern
     * @param string $controller
     * @param array  $middleware
     * @param array  $actions
     */
    public function resource(string $pattern, string $controller, array $middleware = [], array $actions = ['read', 'create', 'update', 'delete']): void
    {
        switch ($this->container->getServer('REQUEST_METHOD')) {
            case 'GET':
                $target = (count($actions)) ? $controller . '::' . $actions[0] : $controller;
                $this->setRoute($pattern, $target, 'GET', $middleware);
                break;
            case 'POST':
                $actionKey  = ['GET' => 0, 'POST' => 1, 'PUT' => 2, 'PATCH' => 2, 'DELETE' => 3];
                $httpMethod = ($this->container->hasPost('_method')) ? $this->container->getPost('_method') : 'POST';
                $target     = (count($actions)) ? $controller . '::' . $actions[$actionKey[$httpMethod]] : $controller;
                $this->setRoute($pattern, $target, $httpMethod, $middleware);
                break;
            case 'PUT':
                $target = (count($actions)) ? $controller . '::' . $actions[2] : $controller;
                $this->setRoute($pattern, $target, 'PUT', $middleware);
                break;
            case 'DELETE':
                $target = (count($actions)) ? $controller . '::' . $actions[3] : $controller;
                $this->setRoute($pattern, $target, 'DELETE', $middleware);
                break;
        }
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
     * @param array $route
     * @return mixed
     */
    abstract public function set(array $route);
}
