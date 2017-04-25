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

namespace Rudra;

/**
 * Class RouterFacade
 *
 * @package Rudra
 */
class RouterFacade
{

    use SetContainerTrait;

    /**
     * @var RequestMethod
     */
    protected $requestMethod;

    /**
     * @var MatchMethod
     */
    protected $matchMethod;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * RouterFacade constructor.
     *
     * @param ContainerInterface $container
     * @param RequestMethod      $requestMethod
     * @param MatchMethod        $matchMethod
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(
        ContainerInterface $container, RequestMethod $requestMethod,
        MatchMethod $matchMethod, string $namespace, string $templateEngine
    ) {
        $this->container      = $container;
        $this->requestMethod  = $requestMethod;
        $this->matchMethod    = $matchMethod;
        $this->namespace      = $namespace;
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    public function setRequestMethod(string $param = null)
    {
        return $this->requestMethod->setRequestMethod($param);
    }

    /**
     * @return MatchMethod
     */
    public function matchMethod(): MatchMethod
    {
        return $this->matchMethod;
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
            $this->matchMethod()->matchHttpMethod($route);
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'PUT')
            || ($this->container()->getServer('REQUEST_METHOD') === 'PATCH')
            || ($this->container()->getServer('REQUEST_METHOD') === 'DELETE')
        ) {
            $settersName = 'set' . ucfirst(strtolower($this->container()->getServer('REQUEST_METHOD')));
            parse_str(file_get_contents('php://input'), $data);
            $this->container()->$settersName($data);
            $this->matchMethod()->matchHttpMethod($route);
        }
    } // @codeCoverageIgnore

    /**
     * @param array $route
     * @param null  $params
     *
     * @throws RouterException
     */
    public function directCallDecorator(array $route, $params = null): void
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
    protected function setClassName(string $className, string $type): string
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
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
     */
    public function annotationDecorator(string $class, string $method, int $number = 0): void
    {
        $controller = $this->setClassName($class, 'controllersNamespace');
        $result     = $this->container()->get('annotation')->getMethodAnnotations($controller, $method);

        if (isset($result['Routing'])) {
            $http_method = $result['Routing'][$number]['method'] ?? 'GET';
            $dataRoute   = $this->setRouteData($class, $method, $number, $result, $http_method);

            $this->set($dataRoute);
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param int    $number
     * @param        $result
     * @param        $http_method
     *
     * @return array
     */
    protected function setRouteData(string $class, string $method, int $number, $result, $http_method)
    {
        $dataRoute = ['pattern'     => $result['Routing'][$number]['url'],
                      'controller'  => $class,
                      'method'      => $method,
                      'http_method' => $http_method
        ];

        if (isset($result['Middleware'])) {
            $dataRoute = array_merge($dataRoute, ['middleware' => $this->handleAnnotationMiddleware($result['Middleware'])]);
        }

        if (isset($result['AfterMiddleware'])) {
            $dataRoute = array_merge($dataRoute, ['after_middleware' => $this->handleAnnotationMiddleware($result['AfterMiddleware'])]);
        }

        return $dataRoute;
    }

    /**
     * @param array $annotation
     *
     * @return array
     */
    protected function handleAnnotationMiddleware(array $annotation): array
    {
        $i          = 0;
        $middleware = [];

        foreach ($annotation as $item) {
            $middleware[$i][] = $item['name'];

            if (isset($item['params'])) {
                $middleware[$i][] = $item['params'];
            }
            $i++;
        }

        return $middleware;
    }

    /**
     * @param array $route
     * @param       $params
     *
     * @return mixed
     */
    public function setCallableDecorator(array $route, $params)
    {
        // Если $route['method'] является экземпляром ксласса Closure
        // возвращаем замыкание
        if ($route['method'] instanceof \Closure) {
            return $route['method']();
        }

        $route['controller'] = $this->setClassName($route['controller'], 'controllersNamespace');
        isset($params) ? $this->directCallDecorator($route, $params) : $this->directCallDecorator($route);
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
