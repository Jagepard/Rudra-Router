<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Interfaces\RudraInterface;

trait RouterRequestMethodTrait
{
    /**
     * @param  array $route
     * @return void
     */
    public function get(array $route): void
    {
        $route['method'] = "GET";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @return void
     */
    public function post(array $route): void
    {
        $route['method'] = "POST";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @return void
     */
    public function put(array $route): void
    {
        $route['method'] = "PUT";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @return void
     */
    public function patch(array $route): void
    {
        $route['method'] = "PATCH";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @return void
     */
    public function delete(array $route): void
    {
        $route['method'] = "DELETE";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @return void
     */
    public function any(array $route): void
    {
        $route['method'] = "GET|POST|PUT|PATCH|DELETE";
        $this->set($route);
    }

    /**
     * @param  array $route
     * @param  array $actions
     * @return void
     */
    public function resource(array $route, array $actions = ["read", "create", "update", "delete"]): void
    {
        switch ($this->rudra->request()->server()->get("REQUEST_METHOD")) {
            case "GET":
                $route['method'] = "GET";
                $route['action'] = $actions[0];

                $this->set($route);
                break;
            case "POST":
                $actionKey       = ["GET" => 0, "POST" => 1, "PUT" => 2, "PATCH" => 2, "DELETE" => 3];
                $httpMethod      = ($this->rudra->request()->post()->has("_method")) ? $this->rudra->request()->post()->get("_method") : "POST";
                $route['method'] = $httpMethod;
                $route['action'] = $actions[$actionKey[$httpMethod]];

                $this->set($route);
                break;
            case "PUT":
                $route['method'] = "PUT";
                $route['action'] = $actions[2];

                $this->set($route);
                break;
            case "DELETE":
                $route['method'] = "DELETE";
                $route['action'] = $actions[3];

                $this->set($route);
                break;
        }
    }

    /**
     * @param array $route
     */
    abstract public function set(array $route): void;

    /**
     * @return RudraInterface
     */
    abstract public function rudra(): RudraInterface;
}
