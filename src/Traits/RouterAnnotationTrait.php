<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Traits;

use Rudra\Interfaces\ContainerInterface;

trait RouterAnnotationTrait
{
    /**
     * @param array $data
     * @param bool  $multilevel
     */
    public function annotationCollector(array $data, bool $multilevel = false)
    {
        if (!$multilevel) {
            $this->handleAnnotation($data);
            return;
        }

        foreach ($data as $subData) {
            $this->handleAnnotation($subData);
        }
    }

    /**
     * @param array $data
     */
    protected function handleAnnotation(array $data): void
    {
        foreach ($data as $item) {
            $this->annotation($item[0], $item[1] ?? 'actionIndex', $item[2] ?? 0);
        }
    }

    /**
     * @param string $controller
     * @param string $action
     * @param int    $number
     */
    public function annotation(string $controller, string $action = 'actionIndex', int $number = 0): void
    {
        $className  = $this->setClassName($controller, $this->namespace . 'Controllers\\');
        $annotation = $this->container()->get('annotation')->getAnnotations($className, $action);

        if (isset($annotation['Routing'])) {
            $httpMethod = $annotation['Routing'][$number]['method'] ?? 'GET';
            $routeData  = $this->setRouteData($controller, $action, $number, $annotation, $httpMethod);

            $this->set($routeData);
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param int    $number
     * @param        $result
     * @param        $httpMethod
     * @return array
     */
    protected function setRouteData(string $class, string $method, int $number, $result, $httpMethod)
    {
        $dataRoute = [
            'controller'  => $class,
            'method'      => $method,
            'http_method' => $httpMethod,
            'pattern'     => $result['Routing'][$number]['url']
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
     * @return array
     */
    protected function handleAnnotationMiddleware(array $annotation): array
    {
        $middleware = [];
        $count      = count($annotation);

        for ($i = 0; $i < $count; $i++) {
            $middleware[$i][] = $annotation[$i]['name'];

            if (isset($annotation[$i]['params'])) {
                $middleware[$i][] = $annotation[$i]['params'];
            }
        }

        return $middleware;
    }

    /**
     * @param array $route
     * @return mixed
     */
    abstract public function set(array $route);

    /**
     * @param string $className
     * @param string $type
     * @return string
     */
    abstract protected function setClassName(string $className, string $type): string;

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
