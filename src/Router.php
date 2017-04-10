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
     * @param string $pattern
     * @param        $classAndMethod
     *
     * @return bool
     */
    public function set(string $pattern, $classAndMethod)
    {
        // Исходные данные для инкремента
        $i = 0;

        // Строка запроса
        $requestUrl = trim($this->container()->getServer('REQUEST_URI'), '/');
        // Разбираем данные $_SERVER['REQUEST_URI'] по '/'
        $requestArray = explode('/', $requestUrl);

        // Обходим элементы массива $pattern
        foreach (explode('/', $pattern) as $itemPattern) {

            if (strpos($itemPattern, '::') !== false) {
                $patternData   = explode('::', $itemPattern);
                $requestMethod = ($patternData[1] !== null) ? $patternData[1] : 'GET';
                $itemPattern   = $patternData[0];

                if (array_key_exists(2, $patternData)) {
                    $hasMethod = 'has' . ucfirst(strtolower($patternData[1]));
                    $getMethod = 'get' . ucfirst(strtolower($patternData[1]));
                    $key       = $patternData[2];

                    if ($this->container()->$hasMethod($key)) {
                        $params[$key] = $this->container()->$getMethod($key);
                    }
                }

            } else {
                $requestMethod = 'GET';
            }

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

        // Если запрашиваем метод совпадаеи с $_SERVER['REQUEST_METHOD']
        // и $realRequestString совпадает с $_SERVER['REQUEST_URI']
        if ($requestMethod == $this->container()->getServer('REQUEST_METHOD') && $realRequestString == $OutRequestUrl[0]) {
            // Устанавливаем token true
            $this->setToken(true);

            // Если $classAndMethod является экземпляром ксласса Closure
            // возвращаем замыкание
            if ($classAndMethod instanceof \Closure) {
                return $classAndMethod();
            }

            isset($params) ? $this->directCall($classAndMethod, $params) : $this->directCall($classAndMethod);

            return false;
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
        $controller->before();
        // Собственно вызываем экшн, в зависимости от наличия параметров
        isset($params) ? $controller->{$method}($params) : $controller->{$method}();
        // Выполняем метод after
        $controller->after();
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
}
