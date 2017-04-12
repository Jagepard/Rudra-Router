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

            $requestUrl                              = trim($this->container()->getServer('REQUEST_URI'), '/');
            $requestArray                            = explode('/', $requestUrl);
            list($params, $completeRequestArray)     = $this->handlePattern($route, $requestArray);
            list($requestString, $realRequestString) = $this->handleCompleteRequestArray($completeRequestArray);
            $outRequestUrl                           = $this->getOutRequestUrl($requestUrl);

            // Это нужно для обработки 404 ошибки
            if (isset($requestString)) {
                // Проверяем строку запроса на соответсвие маршруту
                preg_match("/$requestString/", $requestUrl, $matches);

                // Если совпадений нет, то возвращаем $this->isToken() == false
                if (!isset($matches[0])) {
                    return $this->isToken();
                }
            }

            return $this->setCallable($route, $realRequestString, $outRequestUrl, $params);
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
     * @param array $route
     * @param array $requestArray
     *
     * @return array
     */
    protected function handlePattern(array $route, array $requestArray): array
    {
        $i                    = 0;
        $params               = [];
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

        if (isset($completeRequestArray)) {
            $requestString     = implode('\/', $completeRequestArray);
            $realRequestString = implode('/', $completeRequestArray);
            return array($requestString, $realRequestString);
        }
        return array($requestString, $realRequestString);
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
     * @param       $realRequestString
     * @param       $outRequestUrl
     * @param       $params
     *
     * @return mixed
     */
    protected function setCallable(array $route, $realRequestString, $outRequestUrl, $params)
    {
        // Если $realRequestString совпадает с 'REQUEST_URI'
        if ($realRequestString == $outRequestUrl[0]) {
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

    /**
     * @param array $classAndMethod
     * @param null  $params
     */
    public abstract function directCall(array $classAndMethod, $params = null): void;

    /**
     * @return ContainerInterface
     */
    protected abstract function container(): ContainerInterface;

    /**
     * @return bool
     */
    public abstract function isToken(): bool;

    /**
     * @param bool $token
     */
    public abstract function setToken(bool $token);
}
