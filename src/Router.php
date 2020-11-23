<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Router\Traits\{RouterMatchTrait, RouterRequestMethodTrait, RouterAnnotationTrait};
use Rudra\Exceptions\RouterException;

class Router implements RouterInterface
{
    use RouterMatchTrait;
    use RouterRequestMethodTrait;
    use RouterAnnotationTrait;

    protected ?string $namespace = null;
    protected RudraInterface $rudra;

    public function __construct(RudraInterface $rudra)
    {
        $this->rudra = $rudra;
        set_exception_handler([new RouterException(), "handler"]);
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function set(array $route): void
    {
        $requestMethod = $this->rudra()->request()->server()->get("REQUEST_METHOD");
        if ($this->rudra()->request()->post()->has("_method") && $requestMethod === "POST") {
            $this->rudra()->request()->server()
                ->set(["REQUEST_METHOD" => $this->rudra()->request()->post()->get("_method")]);
        }

        if (in_array($requestMethod, ["PUT", "PATCH", "DELETE"])) {
            parse_str(file_get_contents("php://input"), $data);
            $this->rudra()->request()->{strtolower($requestMethod)}()->set($data);
        }

        $this->handleRequest($route);
    } // @codeCoverageIgnore

    public function directCall(array $route, $params = null): void
    {
        $controller = new $route["controller"]($this->rudra);

        if (!method_exists($controller, $route["method"])) {
            throw new RouterException("503");
        }

        $controller->init();
        $controller->before();
        !isset($route["middleware"]) ?: $this->handleMiddleware($route["middleware"]);
        !isset($params) ? $controller->{$route["method"]}() : $controller->{$route["method"]}(...$params);
        !isset($route["after_middleware"]) ?: $this->handleMiddleware($route["after_middleware"]);
        $controller->after();
        if ($this->rudra()->config()->get("environment") !== "test") exit();
    }

    public function handleMiddleware(array $middleware)
    {
        foreach ($middleware as $current) {
            $middlewareName = $this->setClassName($current[0], $this->namespace . "Middleware\\");
            (new $middlewareName())();
        }
    }

    public function rudra(): RudraInterface
    {
        return $this->rudra;
    }
}
