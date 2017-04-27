<?php

declare(strict_types = 1);

/**
 * Date: 20.04.17
 * Time: 18:42
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router;

use Rudra\Container\ContainerInterface;

/**
 * Class RouterAnnotationTrait
 *
 * @package Rudra
 */
trait RouterAnnotationTrait
{

    /**
     * @param     $class
     * @param     $method
     * @param int $number
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
     * @return ContainerInterface
     */
    protected abstract function container(): ContainerInterface;

    /**
     * @param array $route
     *
     * @return bool
     */
    public abstract function set(array $route);

    /**
     * @param string $className
     * @param string $type
     *
     * @return string
     * @throws RouterException
     */
    protected abstract function setClassName(string $className, string $type): string;
}
