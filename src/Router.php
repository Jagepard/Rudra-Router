<?php

declare(strict_types=1);

/**
 * Date: 05.09.16
 * Time: 14:51
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

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
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var string
     */
    protected $namespace;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     */
    public function __construct(ContainerInterface $container, string $namespace)
    {
        $this->container = $container;
        $this->namespace = $namespace;
        set_exception_handler([new RouterException($container), 'handler']);
    }

    /**
     * @param array $route
     */
    public function set(array $route): void
    {
        $requestMethod = $this->container()->getServer('REQUEST_METHOD');

        if ($this->container()->hasPost('_method') && $requestMethod === 'POST') {
            $this->setRequestMethod();
        }

        if (($requestMethod === 'GET') || $requestMethod === 'POST') {
            $this->matchHttpMethod($route);
        }

        if (($requestMethod === 'PUT') || ($requestMethod === 'PATCH') || ($requestMethod === 'DELETE')) {
            $settersName = 'set' . ucfirst(strtolower($requestMethod));
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

        if (!method_exists($controller, $route['method'])) {
            throw new RouterException($this->container(), '503');
        }

        // Инициализуруем
        $controller->init($this->container());

        // Выполняем методы before до основного вызова
        $controller->before();
        !isset($route['middleware']) ?: $this->handleMiddleware($route['middleware']);
        // Собственно вызываем экшн, в зависимости от наличия параметров
        isset($params) ? $controller->{$route['method']}($params) : $controller->{$route['method']}();
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

        if ($param === 'REST') {

            $route = [];

            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $route = ['http_method' => 'PUT', 'method' => 'update'];
                    break;
                case 'PATCH':
                    $route = ['http_method' => 'PATCH', 'method' => 'update'];
                    break;
                case 'DELETE':
                    $route = ['http_method' => 'DELETE', 'method' => 'delete'];
                    break;
            }

            return $route;
        }
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
    protected function controllersNamespace(): string
    {
        return $this->namespace . 'Controllers\\';
    }

    /**
     * @return mixed
     */
    protected function middlewareNamespace(): string
    {
        return $this->namespace . 'Middleware\\';
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }
}
