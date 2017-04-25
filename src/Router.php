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

namespace Rudra;

/**
 * Class Router
 *
 * @package Rudra
 */
class Router //implements RouterInterface
{

    use SetContainerTrait;

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

        $this->routerFacade = new RouterFacade($this->container, new RequestMethod($this->container), new MatchMethod($this->container));
    }

    /**
     * @var RouterFacade
     */
    protected $routerFacade;

    /**
     * @return RouterFacade
     */
    public function routerFacade(): RouterFacade
    {
        return $this->routerFacade;
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route)
    {
        $this->routerFacade()->set($route);
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
     * @param string $method
     * @param array  $route
     *
     * @return mixed
     */
    public function middleware(string $method, array $route)
    {
        return $this->$method($route);
    }

    /**
     * @param array $route
     */
    public function get(array $route): void
    {
        $route['http_method'] = 'GET';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function post(array $route): void
    {
        $route['http_method'] = 'POST';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function put(array $route): void
    {
        $route['http_method'] = 'PUT';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function patch(array $route): void
    {
        $route['http_method'] = 'PATCH';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function delete(array $route): void
    {
        $route['http_method'] = 'DELETE';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function any(array $route): void
    {
        $route['http_method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->routerFacade()->set($route);
    }

    /**
     * @param array $route
     */
    public function resource(array $route): void
    {
        switch ($this->container()->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
                if ($this->container()->hasPost('_method')) {
                    $route = array_merge($route, $this->setRequestMethod('REST'));
                } else {
                    $route['http_method'] = 'POST';
                    $route['method']      = 'create';
                }
                break;
            case 'PUT':
                $route['http_method'] = 'PUT';
                $route['method']      = 'update';
                break;
            case 'DELETE':
                $route['http_method'] = 'DELETE';
                $route['method']      = 'delete';
                break;
        }

        $this->routerFacade()->set($route);
    }

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
     */
    public function annotation(string $class, string $method, int $number = 0): void
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

    //////////////////////////////////////////////


    /**
     * @param array $route
     *
     * @return bool|void
     */
    protected function matchRequest(array $route)
    {
        if ($route['http_method'] == $this->container()->getServer('REQUEST_METHOD')) {

            $requestUrl   = trim($this->container()->getServer('REQUEST_URI'), '/');
            $requestArray = explode('/', $requestUrl);
            list($params, $completeRequestArray) = $this->handlePattern($route, $requestArray);
            list($requestString, $realRequestString) = $this->handleCompleteRequestArray($completeRequestArray);
            $outRequestUrl = $this->getOutRequestUrl($requestUrl);

            // Это нужно для обработки 404 ошибки
            if (isset($requestString)) {
                // Проверяем строку запроса на соответсвие маршруту
                preg_match("/$requestString/", $requestUrl, $matches);

                // Если совпадений нет, то возвращаем $this->isToken() == false
                if (!isset($matches[0])) {
                    return $this->isToken();
                }
            }

            return $this->handleRequest($route, $realRequestString, $outRequestUrl, $params);
        }
    }

    /**
     * @param array $route
     * @param array $requestArray
     *
     * @return array
     */
    protected function handlePattern(array $route, array $requestArray): array
    {
        $i                    = 0;
        $params               = null;
        $paramsKey            = [];
        $matchesPattern       = [];
        $completeRequestArray = [];

        // Обходим элементы массива $pattern
        foreach (explode('/', ltrim($route['pattern'], '/')) as $itemPattern) {
            // Ищем совпадение строки запроса с шаблоном {...}
            if (preg_match('/{[a-zA-Z0-9]+}/', $itemPattern, $matchesPattern) != 0) {

                // Если есть элемент массива $i
                if (isset($requestArray[$i])) {
                    // Убираем {} из названия будующего ключа массива параметров
                    preg_match('/[a-zA-Z0-9]+/', $matchesPattern[0], $paramsKey);
                    // Присваиваем найденому параметру соответсвующий uri
                    $params[$paramsKey[0]]  = $requestArray[$i];
                    $completeRequestArray[] = $requestArray[$i];
                }
                // Если совпадений нет, то записываем данные не совпадающие
                // с шаблоном в отдельный массив
            } else {
                $completeRequestArray[] = $itemPattern;
            }

            $i++;
        }

        return [$params, $completeRequestArray];
    }

    /**
     * @param $completeRequestArray
     *
     * @return array
     */
    protected function handleCompleteRequestArray($completeRequestArray)
    {
        $requestString     = '';
        $realRequestString = '';

        if (count($completeRequestArray)) {
            $requestString     = implode('\/', $completeRequestArray);
            $realRequestString = implode('/', $completeRequestArray);
        }

        return [$requestString, $realRequestString];
    }

    /**
     * @param array $route
     * @param       $realRequestString
     * @param       $outRequestUrl
     * @param       $params
     */
    protected function handleRequest(array $route, $realRequestString, $outRequestUrl, $params)
    {
        // Если $realRequestString совпадает с 'REQUEST_URI'
        if ($realRequestString == $outRequestUrl[0]) {
            // Устанавливаем token true
            $this->setToken(true);
            $this->setCallable($route, $params);
            (DEV === 'test') ?: exit(); // @codeCoverageIgnore
        }
    }

    /**
     * @param $requestUrl
     *
     * @return mixed
     */
    protected function getOutRequestUrl($requestUrl)
    {
        $outRequestUrl = [];

        if (strpos($requestUrl, '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $requestUrl, $outRequestUrl);

            return $outRequestUrl;
        }

        $outRequestUrl[0] = $requestUrl;

        return $outRequestUrl;
    }

    /**
     * @param array $route
     * @param       $params
     *
     * @return mixed
     */
    protected function setCallable(array $route, $params)
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
     * @var bool
     */
    protected $token = false;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * @var RouterMatch
     */
    protected $routerMatch;

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
