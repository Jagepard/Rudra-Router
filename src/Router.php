<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra;

use Rudra\Traits\RouterMatchTrait;
use Rudra\Traits\RouterRequestMethodTrait;
use Rudra\Interfaces\RouterInterface;
use Rudra\Exceptions\RouterException;
use Rudra\Traits\RouterAnnotationTrait;
use Rudra\Interfaces\ContainerInterface;

class Router implements RouterInterface
{
    use RouterMatchTrait;
    use RouterRequestMethodTrait;
    use RouterAnnotationTrait;

    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Router constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        set_exception_handler([new RouterException($container), 'handler']);
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @param array $route
     * @throws Exceptions\RouterException
     */
    public function set(array $route): void
    {
        $requestMethod = $this->container()->getServer('REQUEST_METHOD');

        if ($this->container()->hasPost('_method') && $requestMethod === 'POST') {
            $this->container()->setServer('REQUEST_METHOD', $this->container()->getPost('_method'));
        }

        if (in_array($requestMethod, ['PUT', 'PATCH', 'DELETE'])) {
            $settersName = 'set' . ucfirst(strtolower($requestMethod));
            parse_str(file_get_contents('php://input'), $data);
            $this->container()->$settersName($data);
        }

        $this->handleRequest($route);
    } // @codeCoverageIgnore

    /**
     * @param array $route
     * @param null  $params
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void
    {
        $controller = new $route['controller']($this->container());

        if (!method_exists($controller, $route['method'])) {
            throw new RouterException($this->container(), '503');
        }

        $controller->init();
        $controller->before();
        !isset($route['middleware']) ?: $this->handleMiddleware($route['middleware']);
        !isset($params) ? $controller->{$route['method']}() : $controller->{$route['method']}(...$params);
        !isset($route['after_middleware']) ?: $this->handleMiddleware($route['after_middleware']);
        $controller->after();
        if (config('env') !== 'test') exit();
    }

    /**
     * @param array $middleware
     * @throws RouterException
     */
    public function handleMiddleware(array $middleware)
    {
        foreach ($middleware as $current) {
            $middlewareName = $this->setClassName($current[0], $this->namespace . 'Middleware\\');
            (new $middlewareName())();
        }
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
