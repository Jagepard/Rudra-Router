<?php

declare(strict_types = 1);

/**
 * Date: 12.04.17
 * Time: 10:01
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router;


use Rudra\Container\ContainerInterface;
use Rudra\Container\SetContainerTrait;
use Rudra\Exception\RouterException;


/**
 * Class Router
 *
 * @package Rudra
 */
class Router
{

    use SetContainerTrait;

    /**
     * @var MatchRequestMethod
     */
    protected $matchRequestMethod;

    /**
     * @var MatchHttpMethod
     */
    protected $matchHttpMethod;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * @var MatchAnnotation
     */
    protected $matchAnnotation;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine)
    {
        $this->container          = $container;
        $this->namespace          = $namespace;
        $this->templateEngine     = $templateEngine;
        $this->matchRequestMethod = new MatchRequestMethod($this->container);
        $this->matchHttpMethod    = new MatchHttpMethod($this->container, new MatchRequest($this->container, $this));
        $this->matchAnnotation    = new MatchAnnotation($this->container, $this);
    }

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    public function setRequestMethod(string $param = null)
    {
        return $this->matchRequestMethod->setRequestMethod($param);
    }

    /**
     * @param array|null $route
     *
     * @return MatchHttpMethod|void
     */
    public function matchHttpMethod(array $route = null)
    {
        return isset($route) ? $this->matchHttpMethod->matchHttpMethod($route) : $this->matchHttpMethod;
    }

    /**
     * @return MatchAnnotation
     */
    public function matchAnnotation(): MatchAnnotation
    {
        return $this->matchAnnotation;
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
     * @param array $middleware
     */
    public function handleMiddleware(array $middleware)
    {
        $current = array_shift($middleware);

        if (is_array($current)) {
            $currentMiddleware = $this->setClassName($current[0], 'middlewareNamespace');

            (new $currentMiddleware($this->container()))($current, $middleware);
        }
    }

    /**
     * @param string $className
     * @param string $type
     *
     * @return string
     * @throws RouterException
     */
    public function setClassName(string $className, string $type): string
    {
        if (strpos($className, '::namespace') !== false) {
            $classNameArray = explode('::', $className);

            if (class_exists($classNameArray[0])) {
                $className = $classNameArray[0];
            } else {
                throw new RouterException('503');
            }
        } else {

            if (class_exists($this->$type() . $className)) {
                $className = $this->$type() . $className;
            } else {
                throw new RouterException('503');
            }
        }

        return $className;
    }

    /**
     * @param array $route
     * @param       $params
     *
     * @return mixed
     */
    public function setCallable(array $route, $params)
    {
        // Если $route['method'] является экземпляром ксласса Closure
        // возвращаем замыкание
        if ($route['method'] instanceof \Closure) {
            return $route['method']();
        }

        $route['controller'] = $this->setClassName($route['controller'], 'controllersNamespace');
        isset($params) ? $this->directCall($route, $params) : $this->directCall($route);
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
