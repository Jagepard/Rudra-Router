<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Interfaces\ApplicationInterface;
use Rudra\Router\Traits\{RouterMatchTrait, RouterRequestMethodTrait, RouterAnnotationTrait};
use Rudra\Exceptions\RouterException;

class Router implements RouterInterface
{
    use RouterMatchTrait;
    use RouterRequestMethodTrait;
    use RouterAnnotationTrait;

    protected ?string $namespace = null;
    protected ApplicationInterface $application;

    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
        set_exception_handler([new RouterException(), "handler"]);
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function set(array $route): void
    {
        $requestMethod = $this->application()->request()->server()->get("REQUEST_METHOD");
        if ($this->application()->request()->post()->has("_method") && $requestMethod === "POST") {
            $this->application()->request()->server()
                ->set(["REQUEST_METHOD" => $this->application()->request()->post()->get("_method")]);
        }

        if (in_array($requestMethod, ["PUT", "PATCH", "DELETE"])) {
            parse_str(file_get_contents("php://input"), $data);
            $this->application()->request()->{strtolower($requestMethod)}()->set($data);
        }

        $this->handleRequest($route);
    } // @codeCoverageIgnore

    public function directCall(array $route, $params = null): void
    {
        $controller = new $route["controller"]($this->application);

        if (!method_exists($controller, $route["method"])) {
            throw new RouterException("503");
        }

        $controller->init();
        $controller->before();
        !isset($route["middleware"]) ?: $this->handleMiddleware($route["middleware"]);
        !isset($params) ? $controller->{$route["method"]}() : $controller->{$route["method"]}(...$params);
        !isset($route["after_middleware"]) ?: $this->handleMiddleware($route["after_middleware"]);
        $controller->after();
        if ($this->application()->config()->get("environment") !== "test") return;
    }

    public function handleMiddleware(array $middleware)
    {
        foreach ($middleware as $current) {
            $middlewareName = $this->setClassName($current[0], $this->namespace . "Middleware\\");
            (new $middlewareName())();
        }
    }

    public function application(): ApplicationInterface
    {
        return $this->application;
    }
}
