<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Traits;

use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;

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
    protected function handleRequest(array $route): void
    {
        if (strpos($route['http_method'], '|') !== false) {

            $httpMethods = explode('|', $route['http_method']);

            foreach ($httpMethods as $httpMethod) {
                $route['http_method'] = $httpMethod;
                $this->matchRequest($route);
            }
        }

        $this->matchRequest($route);
    }

    /**
     * @param array $route
     * @throws RouterException
     */
    protected function matchRequest(array $route): void
    {
        if ($route['http_method'] == $this->container()->getServer('REQUEST_METHOD')) {
            $request = parse_url(trim($this->container()->getServer('REQUEST_URI'), '/'))['path'];
            list($uri, $params) = $this->handlePattern($route, explode('/', $request));
            (implode('/', $uri) !== $request) ?: $this->setCallable($route, $params);
        }
    }

    /**
     * @param array $route
     * @param array $request
     * @return array
     */
    protected function handlePattern(array $route, array $request): array
    {
        $uri     = [];
        $params  = null;
        $pattern = explode('/', ltrim($route['pattern'], '/'));
        $count   = count($pattern);

        for ($i = 0; $i < $count; $i++) {
            // Ищем совпадение с шаблоном {...}
            if (preg_match('/{([a-zA-Z0-9]*?)}/', $pattern[$i]) !== 0) {
                if (array_key_exists($i, $request)) {
                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                }
                continue;
            }

            $uri[] = $pattern[$i];
        }

        return [$uri, $params];
    }

    /**
     * @param array $route
     * @param       $params
     * @return mixed
     * @throws RouterException
     */
    protected function setCallable(array $route, $params)
    {
        if ($route['method'] instanceof \Closure) {
            return $route['method']();
        }

        $route['controller'] = $this->setClassName($route['controller'], $this->namespace . 'Controllers\\');
        $this->directCall($route, $params);
    }

    /**
     * @param string $className
     * @param string $namespace
     * @return string
     * @throws RouterException
     */
    protected function setClassName(string $className, string $namespace): string
    {
        $className = (strpos($className, ':fq') !== false)
            ? explode(':', $className)[0]
            : $namespace . $className;

        if (!class_exists($className)) {
            throw new RouterException($this->container(), '503');
        }

        return $className;
    }

    /**
     * @param array      $route
     * @param null|array $params
     * @throws RouterException
     */
    abstract public function directCall(array $route, $params = null): void;

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
