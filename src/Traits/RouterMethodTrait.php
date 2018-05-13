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
     * @param array  $route
     * @return mixed
     */
    public function middleware(string $method, array $route)
    {
        return $this->$method($route);
    }

    /**
     * @param string $pattern
     * @param        $target
     * @param string $httpMethod
     */
    protected function setRoute(string $pattern, $target, string $httpMethod): void
    {
        $route['http_method'] = $httpMethod;
        $route['pattern']     = $pattern;

        (is_callable($target))
            ? $route['method'] = $target
            : list($route['controller'], $route['method']) = explode('::', $target);

        $this->set($route);
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
     * @param array $route
     */
    public function put(array $route): void
    {
        $route['http_method'] = 'PUT';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function patch(array $route): void
    {
        $route['http_method'] = 'PATCH';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function delete(array $route): void
    {
        $route['http_method'] = 'DELETE';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function any(array $route): void
    {
        $route['http_method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function resource(array $route): void
    {
        switch ($this->container->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
                if ($this->container->hasPost('_method')) {
                    $route = array_merge($route, $this->setRequestMethod('REST'));
                    break;
                }
                $route['http_method'] = 'POST';
                $route['method']      = 'create';
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
