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

    protected ?string        $namespace = null;

    protected RudraInterface $rudra;

    /**
     * Router constructor.
     *
     * @param  \Rudra\Container\Interfaces\RudraInterface  $rudra
     */
    public function __construct(RudraInterface $rudra)
    {
        $this->rudra = $rudra;
        set_exception_handler([new RouterException(), "handler"]);
    }

    /**
     * @param  string  $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @param  array  $route
     * @param  null  $params
     *
     * @throws \Rudra\Exceptions\RouterException
     */
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
        $this->handleParams($params, $route["action"], $controller);
        !isset($route["after_middleware"]) ?: $this->handleMiddleware($route["after_middleware"]);
        $controller->after();

        if ($this->rudra()->config()->get("environment") !== "test") {
            exit();
        }
    }

    /**
     * @param  array  $route
     * @param $params
     *
     * @throws \Rudra\Exceptions\RouterException
     */
    protected function setCallable(array $route, $params)
    {
        if ($route["action"] instanceof \Closure) {
            (is_array($params)) ? $route["action"](...$params) : $route["action"]($params);
            return;
        }

        $route["controller"] = $this->setClassName($route["controller"], $this->namespace . "Controllers\\");
        $this->directCall($route, $params);
    }

    /**
     * @param  string  $className
     * @param  string  $namespace
     *
     * @return string
     * @throws \Rudra\Exceptions\RouterException
     */
    protected function setClassName(string $className, string $namespace): string
    {
        $className = (strpos($className, ":fq") !== false)
            ? explode(':', $className)[0]
            : $namespace.$className;

        if (!class_exists($className)) {
            throw new RouterException("503");
        }

        return $className;
    }

    /**
     * @return \Rudra\Container\Interfaces\RudraInterface
     */
    public function rudra(): RudraInterface
    {
        return $this->rudra;
    }

    /**
     * @param $params
     * @param $action
     * @param $controller
     *
     * @throws \Rudra\Exceptions\RouterException
     */
    protected function handleParams($params, $action, $controller): void
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

}
