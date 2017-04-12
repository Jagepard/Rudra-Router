<?php

declare(strict_types = 1);

/**
 * Date: 12.04.17
 * Time: 10:34
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterMatchTrait
 *
 * @package Rudra
 */
trait RouterMatchTrait
{

    /**
     * @param array $route
     */
    protected function matchHttpMethod(array $route)
    {
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

                $controller = $this->controllerName($route['controller']);

                isset($params)
                    ? $this->directCall([$controller, $route['method']], $params)
                    : $this->directCall([$controller, $route['method']]);
            }
        }
    }

    /**
     * @param $controllerName
     *
     * @return string
     * @throws RouterException
     */
    protected function controllerName($controllerName)
    {
        if (strpos($controllerName, '::namespace') !== false) {
            $controllerArray = explode('::', $controllerName);

            if (class_exists($controllerArray[0])) {
                $controller = $controllerArray[0];
            } else {
                throw new RouterException('503');
            }
        } else {

            if (class_exists($this->namespace() . $controllerName)) {
                $controller = $this->namespace() . $controllerName;
            } else {
                throw new RouterException('503');
            }
        }

        return $controller;
    }

    /**
     * @param array $classAndMethod
     * @param null  $params
     */
    public function directCall(array $classAndMethod, $params = null): void
    {
        $controller = $this->container()->new($classAndMethod[0]);
        $method     = $classAndMethod[1];

        // Инициализуруем
        $controller->init($this->container(), $this->templateEngine());
        // Выполняем метод before до основного вызова
        $controller->before(); // --- middleware before
        // Собственно вызываем экшн, в зависимости от наличия параметров
        isset($params) ? $controller->{$method}($params) : $controller->{$method}();
        // Выполняем метод after
        $controller->after(); // --- middleware after
    }
}