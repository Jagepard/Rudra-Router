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
     * @param       $middleware
     */
    protected function matchHttpMethod(array $route, $middleware)
    {
        if (strpos($route['http_method'], '|') !== false) {
            $httpArray = explode('|', $route['http_method']);

            foreach ($httpArray as $httpItem) {
                $route['http_method'] = $httpItem;
                $this->matchRequest($route, $middleware);
            }
        } else {
            $this->matchRequest($route, $middleware);
        }
    }

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

        if (isset($completeRequestArray)) {
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
        }
    }

    /**
     * @param array $middleware
     */
    protected function handleMiddleware(array $middleware)
    {
        if (isset($middleware)) {
            $middleware[0][0] = $this->setClassName($middleware[0][0], 'middlewareNamespace');
            (new $middleware[0][0]($this->container()))($middleware);
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
