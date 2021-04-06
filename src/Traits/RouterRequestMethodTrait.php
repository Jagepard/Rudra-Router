<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

trait RouterRequestMethodTrait
{

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function get(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "GET", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function post(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "POST", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function put(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "PUT", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function patch(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "PATCH", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function delete(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "DELETE", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  array  $middleware
     */
    public function any(string $pattern, $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, "GET|POST|PUT|PATCH|DELETE", $middleware);
    }

    /**
     * @param  string  $pattern
     * @param  string  $controller
     * @param  array  $middleware
     * @param  array|string[]  $actions
     */
    public function resource(
        string $pattern, string $controller, array $middleware = [],
        array $actions = ["read", "create", "update", "delete"]
    ): void
    {
        switch ($this->rudra()->request()->server()->get("REQUEST_METHOD")) {
            case "GET":
                $target = (count($actions)) ? $controller . '::' . $actions[0] : $controller;
                $this->setRoute($pattern, $target, "GET", $middleware);
                break;
            case "POST":
                $actionKey  = ["GET" => 0, "POST" => 1, "PUT" => 2, "PATCH" => 2, "DELETE" => 3];
                $httpMethod = ($this->rudra()->request()->post()->has("_method"))
                    ? $this->rudra()->request()->post()->get("_method") : "POST";
                $target     = (count($actions)) ? $controller . '::' . $actions[$actionKey[$httpMethod]] : $controller;
                $this->setRoute($pattern, $target, $httpMethod, $middleware);
                break;
            case "PUT":
                $target = (count($actions)) ? $controller . "::" . $actions[2] : $controller;
                $this->setRoute($pattern, $target, "PUT", $middleware);
                break;
            case "DELETE":
                $target = (count($actions)) ? $controller . "::" . $actions[3] : $controller;
                $this->setRoute($pattern, $target, "DELETE", $middleware);
                break;
        }
    }

    /**
     * @param  string  $pattern
     * @param $target
     * @param  string  $httpMethod
     * @param  array  $middleware
     */
    protected function setRoute(string $pattern, $target, string $httpMethod, array $middleware = []): void
    {
        $route["http_method"] = $httpMethod;
        $route["pattern"]     = $pattern;

        if (count($middleware)) {
            if (array_key_exists("before", $middleware)) {
                $route["middleware"] = $middleware["before"];
            }

            if (array_key_exists("after", $middleware)) {
                $route["after_middleware"] = $middleware["after"];
            }
        }

        (is_callable($target))
            ? $route["action"] = $target
            : list($route["controller"], $route["action"]) = explode("::", $target);

        $this->handleRequestMethod($route);
    }
}
