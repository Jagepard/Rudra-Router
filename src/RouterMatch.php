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
 * Class RouterMatch
 *
 * @package Rudra
 */
class RouterMatch
{

    /**
     * @param array $route
     */
    public function matchHttpMethod(array $route)
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

//    /**
//     * @param array $classAndMethod
//     * @param null  $params
//     */
//    public abstract function directCall(array $classAndMethod, $params = null): void;
//
//    /**
//     * @return ContainerInterface
//     */
//    protected abstract function container(): ContainerInterface;
//
//    /**
//     * @return bool
//     */
//    public abstract function isToken(): bool;
//
//    /**
//     * @param bool $token
//     */
//    public abstract function setToken(bool $token);
}
