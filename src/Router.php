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
class Router
{

    /**
     * @var bool
     */
    protected $token = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var
     */
    protected $namespace;

    /**
     * @var
     */
    protected $templateEngine;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param                    $namespace
     * @param                    $templateEngine
     */
    public function __construct(ContainerInterface $container, $namespace, $templateEngine)
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
        if ($this->container()->hasPost('_method')) {
            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $this->setRequestMethod('PUT');
                    break;
                case 'PATCH':
                    $this->setRequestMethod('PATCH');
                    break;
                case 'DELETE':
                    $this->setRequestMethod('DELETE');
                    break;
            }
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'GET') ||
            $this->container()->getServer('REQUEST_METHOD') === 'POST'
        ) {
            $this->matchHttpMethod($route);
            return false;
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'PUT') ||
            ($this->container()->getServer('REQUEST_METHOD') === 'PATCH') ||
            ($this->container()->getServer('REQUEST_METHOD') === 'DELETE')
        ) {
            $this->parsePhpInput($this->container()->getServer('REQUEST_METHOD'));
            $this->matchHttpMethod($route);
            return false;
        }
    }

    /**
     * @param $requestMethodName
     */
    protected function parsePhpInput($requestMethodName)
    {
        $settersName = 'set' . ucfirst(strtolower($requestMethodName));
        parse_str(file_get_contents('php://input'), $data);
        var_dump($settersName);
        $this->container()->$settersName($data);
    }

    /**
     * @param array $route
     */
    public function resource(array $route)
    {
        switch ($this->container()->getServer('REQUEST_METHOD')) {
            case 'GET':
                $route['http_method'] = 'GET';
                $route['method']      = 'read';
                break;
            case 'POST':
                $route['http_method'] = 'POST';
                $route['method']      = 'create';
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

        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function get(array $route)
    {
        $route['http_method'] = 'GET';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function post(array $route)
    {
        $route['http_method'] = 'POST';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function put(array $route)
    {
        $route['http_method'] = 'PUT';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function patch(array $route)
    {
        $route['http_method'] = 'PATCH';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function delete(array $route)
    {
        $route['http_method'] = 'DELETE';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    public function any(array $route)
    {
        $route['http_method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->set($route);
    }

    /**
     * @param array $route
     */
    protected function matchHttpMethod(array $route)
    {
        var_dump($route);
        if (strpos($route['http_method'], '|') !== false) {
            $httpArray = explode('|', $route['http_method']);

            foreach ($httpArray as $httpItem) {
                $route['http_method'] = $httpItem;
                $this->matchRequest($route);
            }
        } else {
            $this->matchRequest($route);
        }
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    protected function matchRequest(array $route)
    {
        if ($route['http_method'] == $this->container()->getServer('REQUEST_METHOD')) {

            // Строка запроса
            $requestUrl = trim($this->container()->getServer('REQUEST_URI'), '/');
            // Разбираем данные $_SERVER['REQUEST_URI'] по '/'
            $requestArray = explode('/', $requestUrl);

            // Исходные данные для инкремента
            $i = 0;
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

                // Инкремент
                $i++;
            }

            if (isset($completeRequestArray)) {
                $requestString     = implode('\/', $completeRequestArray);
                $realRequestString = implode('/', $completeRequestArray);
            }

            if (strpos($requestUrl, '?') !== false) {
                preg_match('~[/[:word:]-]+(?=\?)~', $requestUrl, $OutRequestUrl);
            } else {
                $OutRequestUrl[0] = $requestUrl;
            }

            // Это нужно для обработки 404 ошибки
            if (isset($requestString)) {
                // Проверяем строку запроса на соответсвие маршруту
                preg_match("/$requestString/", $requestUrl, $matches);

                // Если совпадений нет, то возвращаем $this->isToken() == false
                if (!isset($matches[0])) {
                    return $this->isToken();
                }
            }

            // Если $realRequestString совпадает с $_SERVER['REQUEST_URI']
            if ($realRequestString == $OutRequestUrl[0]) {
                // Устанавливаем token true
                $this->setToken(true);

                // Если $route['method'] является экземпляром ксласса Closure
                // возвращаем замыкание
                if ($route['method'] instanceof \Closure) {
                    return $route['method']();
                }

                isset($params)
                    ? $this->directCall([$route['controller'], $route['method']], $params)
                    : $this->directCall([$route['controller'], $route['method']]);
            }
        }
    }

    /**
     * @param array $classAndMethod
     * @param null  $params
     */
    public function directCall(array $classAndMethod, $params = null)
    {
        $controller = $this->container()->new($classAndMethod[0]);
        $method     = $classAndMethod[1];

        // Инициализуруем
        $controller->init($this->container(), $this->getTemplateEngine());
        // Выполняем метод before до основного вызова
        $controller->before(); // --- middleware before
        // Собственно вызываем экшн, в зависимости от наличия параметров
        isset($params) ? $controller->{$method}($params) : $controller->{$method}();
        // Выполняем метод after
        $controller->after(); // --- middleware after
    }

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
     */
    public function annotation($class, $method, $number = 0)
    {
        /* class with namespace */
        if (strpos($class, '::namespace') !== false) {
            $classArray = explode('::', $class);

            if (class_exists($classArray[0])) {
                $class = $classArray[0];
            } else {
                throw new RouterException('503');
            }
        } else {

            if (class_exists($this->namespace() . $class)) {
                $class = $this->namespace() . $class;
            } else {
                throw new RouterException('503');
            }
        }

        $result = $this->container()->get('annotation')->getMethodAnnotations($class, $method);

        if (isset($result['Routing'])) {
            $this->set($result['Routing'][$number]['url'], [$class, $method]);
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
    public function container(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    public function namespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * @param $requestMethod
     */
    protected function setRequestMethod($requestMethod)
    {
        $this->container()->setServer('REQUEST_METHOD', $requestMethod);
    }
}
