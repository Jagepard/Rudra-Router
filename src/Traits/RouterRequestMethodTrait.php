<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Facades\Request;

trait RouterRequestMethodTrait
{
    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function get(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "GET", $target], $middleware));
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function post(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "POST", $target], $middleware));
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function put(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "PUT", $target], $middleware));        
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function patch(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "PATCH", $target], $middleware));
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function delete(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "DELETE", $target], $middleware));
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function any(string $pattern, $target, array $middleware = []): void
    {
        $this->set(array_merge([$pattern, "GET|POST|PUT|PATCH|DELETE", $target], $middleware));
    }

    /**
     * @param  string  $pattern
     * @param  string  $controller
     * @param  array  $middleware
     * @param  array|string[]  $actions
     */
    public function resource(string $pattern, string $controller, array $middleware = [], array $actions = ["read", "create", "update", "delete"]): void
    {
        switch (Request::server()->get("REQUEST_METHOD")) {
            case "GET":
                $this->set(array_merge([$pattern, "GET", [$controller, $actions[0]]], $middleware));
                break;
            case "POST":
                $actionKey  = ["GET" => 0, "POST" => 1, "PUT" => 2, "PATCH" => 2, "DELETE" => 3];
                $httpMethod = (Request::post()->has("_method")) ? Request::post()->get("_method") : "POST";
                $this->set(array_merge([$pattern, $httpMethod, [$controller, $actions[$actionKey[$httpMethod]]]], $middleware));
                break;
            case "PUT":
                $this->set(array_merge([$pattern, "PUT", [$controller, $actions[2]]], $middleware));
                break;
            case "DELETE":
                $this->set(array_merge([$pattern, "DELETE", [$controller, $actions[3]]], $middleware));
                break;
        }
    }

    /**
     * @param array $route
     */
    abstract public function set(array $route): void;
}
