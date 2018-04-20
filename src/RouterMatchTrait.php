<?php

declare(strict_types=1);

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
    protected function matchHttpMethod(array $route): void
    {
        if (!strpos($route['http_method'], '|') !== false) {
            $this->matchRequest($route);
        }

        foreach (explode('|', $route['http_method']) as $httpItem) {
            $route['http_method'] = $httpItem;
            $this->matchRequest($route);
        }
    }

    /**
     * @param array $route
     */
    protected function matchRequest(array $route): void
    {
        if ($route['http_method'] == $this->container()->getServer('REQUEST_METHOD')) {

            $requestString = trim($this->container()->getServer('REQUEST_URI'), '/');
            $parsedRequest = parse_url($requestString)['path'];

            list($completeRequestArray, $params) = $this->handlePattern($route, explode('/', $requestString));
            (implode('/', $completeRequestArray) !== $parsedRequest) ?: $this->setCallable($route, $params);
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

        return [$completeRequestArray, $params];
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

            if (!class_exists($classNameArray[0])) {
                throw new RouterException('503');
            }

            return $classNameArray[0];
        }

        if (!class_exists($this->$type() . $className)) {
            throw new RouterException('503');
        }

        return $this->$type() . $className;
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
}
