<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Router\Traits\{RouterHandlerTrait, RouterRequestMethodTrait, RouterAnnotationTrait};
use Rudra\Exceptions\RouterException;

class Router implements RouterInterface
{
    use RouterHandlerTrait;
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

    public function directCall(array $route, $params = null): void
    {
        $controller = new $route["controller"]($this->rudra);

        if (!method_exists($controller, $route["action"])) {
            throw new RouterException("503");
        }

        $controller->eventRegistration();
        $controller->generalPreCall();
        $controller->init();
        $controller->before();
        !isset($route["middleware"]) ?: $this->handleMiddleware($route["middleware"]);
        !isset($params) ? $controller->{$route["action"]}() : $controller->{$route["action"]}(...$params);
        !isset($route["after_middleware"]) ?: $this->handleMiddleware($route["after_middleware"]);
        $controller->after();
        if ($this->rudra()->config()->get("environment") !== "test") exit();
    }

    protected function setCallable(array $route, $params)
    {
        if ($route["action"] instanceof \Closure) {
            (is_array($params)) ? $route["action"](...$params) : $route["action"]($params);
            return;
        }

        $route["controller"] = $this->setClassName($route["controller"], $this->namespace . "Controllers\\");
        $this->directCall($route, $params);
    }

    protected function setClassName(string $className, string $namespace): string
    {
        $className = (strpos($className, ":fq") !== false)
            ? explode(':', $className)[0]
            : $namespace . $className;

        if (!class_exists($className)) {
            throw new RouterException("503");
        }

        return $className;
    }

    public function rudra(): RudraInterface
    {
        return $this->rudra;
    }
}
