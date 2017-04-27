<?php

declare(strict_types = 1);

/**
 * Date: 05.09.16
 * Time: 14:51
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router;

use Rudra\Container\ContainerInterface;
use Rudra\Exception\RouterException;

/**
 * Class Router
 *
 * @package Rudra
 */
class Router implements RouterInterface
{

    use RouterMethodTrait;
    use RouterMatchTrait;
    use RouterAnnotationTrait;

    /**
     * @var bool
     */
    protected $token = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine)
    {
        $this->container      = $container;
        $this->namespace      = $namespace;
        $this->templateEngine = $templateEngine;
        set_exception_handler([new RouterException(), 'handler']);
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route)
    {
        if ($this->container()->hasPost('_method') && $this->container()->getServer('REQUEST_METHOD') === 'POST') {
            $this->setRequestMethod();
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'GET')
            || $this->container()->getServer('REQUEST_METHOD') === 'POST'
        ) {
            $this->matchHttpMethod($route);
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'PUT')
            || ($this->container()->getServer('REQUEST_METHOD') === 'PATCH')
            || ($this->container()->getServer('REQUEST_METHOD') === 'DELETE')
        ) {
            $settersName = 'set' . ucfirst(strtolower($this->container()->getServer('REQUEST_METHOD')));
            parse_str(file_get_contents('php://input'), $data);
            $this->container()->$settersName($data);
            $this->matchHttpMethod($route);
        }
    } // @codeCoverageIgnore

    /**
     * @param array $route
     * @param null  $params
     *
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void
    {
        $controller = new $route['controller']($this->container());

        if (method_exists($controller, $route['method'])) {
            $method = $route['method'];
        } else {
            throw new RouterException('503');
        }

        // Инициализуруем
        $controller->init($this->container(), $this->templateEngine());
        // Выполняем методы before до основного вызова
        $controller->before();
        !isset($route['middleware']) ?: $this->handleMiddleware($route['middleware']);
        // Собственно вызываем экшн, в зависимости от наличия параметров
        isset($params) ? $controller->{$method}($params) : $controller->{$method}();
        // Выполняем методы after
        !isset($route['after_middleware']) ?: $this->handleMiddleware($route['after_middleware']);
        $controller->after(); // after
    }


    /**
     * @param string|null $param
     *
     * @return mixed
     */
    protected function setRequestMethod(string $param = null)
    {
        if ($param === 'REST') {
            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $this->container()->setServer('REQUEST_METHOD', 'PUT');
                    $route['http_method'] = 'PUT';
                    $route['method']      = 'update';

                    return $route;
                case 'PATCH':
                    $this->container()->setServer('REQUEST_METHOD', 'PATCH');
                    $route['http_method'] = 'PATCH';
                    $route['method']      = 'update';

                    return $route;
                case 'DELETE':
                    $this->container()->setServer('REQUEST_METHOD', 'DELETE');
                    $route['http_method'] = 'DELETE';
                    $route['method']      = 'delete';

                    return $route;
            }
        } else {
            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $this->container()->setServer('REQUEST_METHOD', 'PUT');
                    break;
                case 'PATCH':
                    $this->container()->setServer('REQUEST_METHOD', 'PATCH');
                    break;
                case 'DELETE':
                    $this->container()->setServer('REQUEST_METHOD', 'DELETE');
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    /**
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    protected function controllersNamespace()
    {
        return $this->namespace . 'Controllers\\';
    }

    /**
     * @return mixed
     */
    protected function middlewareNamespace()
    {
        return $this->namespace . 'Middleware\\';
    }

    /**
     * @return mixed
     */
    protected function templateEngine()
    {
        return $this->templateEngine;
    }
}
