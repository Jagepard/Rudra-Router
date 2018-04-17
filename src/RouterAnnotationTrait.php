<?php

declare(strict_types=1);

/**
 * Date: 20.04.17 Updated 14.04.18
 * Time: 18:42
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterAnnotationTrait
 *
 * @package Rudra
 */
trait RouterAnnotationTrait
{

    /**
     * @param array $data
     * @throws RouterException
     */
    public function annotationCollector(array $data)
    {
        foreach ($data as $chunk) {
            foreach ($chunk as $item) {
                $this->annotation($item[0], $item[1] ?? 'actionIndex', $item[2] ?? 0);
            }
        }
    }

    /**
     * @param string $controller
     * @param string $action
     * @param int    $number
     * @throws RouterException
     */
    public function annotation(string $controller, string $action = 'actionIndex', int $number = 0): void
    {
        $annotation = $this->container()->get('annotation')
            ->getMethodAnnotations($this->setClassName($controller, 'controllersNamespace'), $action);

        if (isset($annotation['Routing'])) {
            $this->set($this->setRouteData($controller, $action, $number, $annotation,
                $annotation['Routing'][$number]['method'] ?? 'GET')
            );
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param int    $number
     * @param        $result
     * @param        $httpMethod
     *
     * @return array
     */
    protected function setRouteData(string $class, string $method, int $number, $result, $httpMethod)
    {
        $dataRoute = ['pattern'     => $result['Routing'][$number]['url'],
                      'controller'  => $class,
                      'method'      => $method,
                      'http_method' => $httpMethod
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
