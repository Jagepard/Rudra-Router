<?php

declare(strict_types = 1);

/**
 * Date: 12.04.17
 * Time: 10:01
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterMethodTrait
 *
 * @package Rudra
 */
trait RouterMethodTrait
{

    /**
     * @param array $middleware
     * @param       $method
     * @param array $route
     */
    public function middleware(array $middleware, $method, array $route)
    {
        return $this->$method($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function get(array $route, $middleware = null): void
    {
        $route['http_method'] = 'GET';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function post(array $route, $middleware = null): void
    {
        $route['http_method'] = 'POST';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function put(array $route, $middleware = null): void
    {
        $route['http_method'] = 'PUT';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function patch(array $route, $middleware = null): void
    {
        $route['http_method'] = 'PATCH';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function delete(array $route, $middleware = null): void
    {
        $route['http_method'] = 'DELETE';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function any(array $route, $middleware = null): void
    {
        $route['http_method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->set($route, $middleware);
    }

    /**
     * @param array $route
     * @param null  $middleware
     */
    public function resource(array $route, $middleware = null): void
    {
        switch ($this->container()->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
                if ($this->container()->hasPost('_method')) {
                    $route = array_merge($route, $this->setRequestMethod('REST'));
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

        $this->set($route, $middleware);
    }

    /**
     * @return ContainerInterface
     */
    protected abstract function container(): ContainerInterface;

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    protected abstract function setRequestMethod(string $param = null);

    /**
     * @param array $route
     * @param null  $middleware
     *
     * @return mixed
     */
    public abstract function set(array $route, $middleware = null);
}
