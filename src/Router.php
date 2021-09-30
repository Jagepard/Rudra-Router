<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Facades\Request;
use Rudra\Container\Facades\Rudra;
use Rudra\Exceptions\RouterException;
use Rudra\Router\Traits\RouterRequestMethodTrait;

class Router implements RouterInterface
{
    use RouterRequestMethodTrait;

    /**
     * @param  array  $route
     */
    public function set(array $route): void
    {
        if (strpos($route[1], '|') !== false) {
            $httpMethods = explode('|', $route[1]);

            foreach ($httpMethods as $httpMethod) {
                $route[1] = $httpMethod;
                $this->handleRequestUri($route);
            }
        }

        $this->handleRequestUri($route);
    }

    /**
     * @param array $route
     * @param null $params
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void
    {
        if ((count($route) < 3) or (count($route[2]) !== 2)) {
            throw new RouterException("503");
        }

        $controller = new $route[2][0];
        $action = $route[2][1];

        if (!method_exists($controller, $action)) {
            throw new RouterException("503");
        }

        $controller->eventRegistration();
        $controller->generalPreCall();
        $controller->init();
        $controller->before();
        !isset($route["before"]) ?: $this->handleMiddleware($route["before"]);
        $this->callAction($params, $action, $controller);
        !isset($route["after"]) ?: $this->handleMiddleware($route["after"]);
        $controller->after();

        if (Rudra::config()->get("environment") !== "test") {
            exit();
        }
    }

    protected function handleRequestMethod(): void
    {
        $requestMethod = Request::server()->get("REQUEST_METHOD");

        if ($requestMethod === "POST" && Request::post()->has("_method")) {
            Request::server()->set(["REQUEST_METHOD" => Request::post()->get("_method")]);
        }

        if (in_array($requestMethod, ["PUT", "PATCH", "DELETE"])) {
            parse_str(file_get_contents("php://input"), $data);
            Request::{strtolower($requestMethod)}()->set($data);
        }
    }

    protected function handleRequestUri(array $route)
    {
        $this->handleRequestMethod();

        if ($route[1] == Request::server()->get("REQUEST_METHOD")) {
            $requestString = parse_url(ltrim(Request::server()->get("REQUEST_URI"), '/'))["path"];
            [$uri, $params] = $this->handlePattern($route, explode('/', $requestString));
            if (implode('/', $uri) === $requestString) $this->setCallable($route, $params);
        }
    }

    protected function setCallable(array $route, $params)
    {
        if ($route[2] instanceof \Closure) {
            (is_array($params)) ? $route[2](...$params) : $route[2]($params);
            return;
        }

        $this->directCall($route, $params);
    }

    protected function callAction($params, $action, $controller): void
    {
        if (!isset($params)) {
            $controller->{$action}();
        } else {
            if (!in_array("", $params)) {
                $controller->{$action}(...$params);
            } else {
                throw new RouterException("404");
            }
        }
    }

    protected function handlePattern(array $route, array $request): array
    {
        $uri     = [];
        $params  = null;
        $pattern = explode('/', ltrim($route[0], '/'));
        $count   = count($pattern);

        for ($i = 0; $i < $count; $i++) {
            // Looking for a match with a pattern {...}
            if (preg_match('/{([a-zA-Z0-9_]*?)}/', $pattern[$i]) !== 0) {
                if (array_key_exists($i, $request)) {
                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                }
                continue;
            }

            $uri[] = $pattern[$i];
        }

        return [$uri, $params];
    }

    public function handleMiddleware(array $middleware)
    {
        foreach ($middleware as $current) {
            (new $current())();
        }
    }
}
