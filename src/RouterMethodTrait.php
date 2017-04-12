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
     * @param array $route
     */
    public function get(array $route): void
    {
        $route['http_method'] = 'GET';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function post(array $route): void
    {
        $route['http_method'] = 'POST';
        $this->set($route);
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
        switch ($this->container()->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
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
}
