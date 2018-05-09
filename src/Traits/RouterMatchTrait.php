<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Traits;

use Rudra\Exceptions\RouterException;

/**
 * Trait RouterMatchTrait
 * @package Rudra\Traits
 */
trait RouterMatchTrait
{

    /**
     * @param array $route
     * @throws RouterException
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
     * @throws RouterException
     */
    protected function matchRequest(array $route): void
    {
        if ($route['http_method'] == $this->container->getServer('REQUEST_METHOD')) {
            $parsedRequest = parse_url(trim($this->container->getServer('REQUEST_URI'), '/'))['path'];
            list($patternsArray, $params) = $this->handlePattern($route, explode('/', $parsedRequest));
            (implode('/', $patternsArray) !== $parsedRequest) ?: $this->setCallable($route, $params);
        }
    }

    /**
     * @param array $route
     * @param array $parsedRequest
     * @return array
     */
    protected function handlePattern(array $route, array $parsedRequest): array
    {
        $i             = 0;
        $params        = null;
        $patternsArray = [];

        foreach (explode('/', ltrim($route['pattern'], '/')) as $patternItem) {
            // Ищем совпадение с шаблоном {...}
            if (preg_match('/{[a-zA-Z0-9]+}/', $patternItem, $matchesPattern) != 0) {
                // Убираем {} из названия будующего ключа массива параметров
                preg_match('/[a-zA-Z0-9]+/', $matchesPattern[0], $key);
                $params[$key[0]] = $parsedRequest[$i];
                $patternsArray[] = $parsedRequest[$i];
            } else {
                $patternsArray[] = $patternItem;
            }

            $i++;
        }

        return [$patternsArray, $params];
    }

    /**
     * @param array $middleware
     * @throws RouterException
     */
    public function handleMiddleware(array $middleware)
    {
        $current = array_shift($middleware);

        if (is_array($current)) {
            $currentMiddleware = $this->setClassName($current[0], 'middlewareNamespace');

            (new $currentMiddleware($this->container))($current, $middleware);
        }
    }

    /**
     * @param array $route
     * @param       $params
     * @return mixed
     * @throws RouterException
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
     * @return string
     * @throws RouterException
     */
    protected function setClassName(string $className, string $type): string
    {
        if (strpos($className, '::namespace') !== false) {
            $classNameArray = explode('::', $className);

            if (!class_exists($classNameArray[0])) {
                throw new RouterException($this->container, '503');
            }

            return $classNameArray[0];
        }

        if (!class_exists($this->$type() . $className)) {
            throw new RouterException($this->container, '503');
        }

        return $this->$type() . $className;
    }

    /**
     * @param array $classAndMethod
     * @param null  $params
     */
    public abstract function directCall(array $classAndMethod, $params = null): void;
}
