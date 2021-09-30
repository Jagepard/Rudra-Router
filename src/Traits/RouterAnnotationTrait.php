<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Annotation\Annotation;
use Rudra\Container\Facades\Rudra;

trait RouterAnnotationTrait
{
    public function annotationCollector(array $controllers)
    {
        foreach ($controllers as $controller) {
            $methods = get_class_methods($controller);

            foreach ($methods as $method) {
                $annotation = Rudra::get(Annotation::class)->getAnnotations($controller, $method);

                if (isset($annotation["Routing"])) {
                    foreach ($annotation["Routing"] as $route) {
                        $this->set([$route['url'], $route["method"] ?? "GET", [$controller, $method]]);
                    }
                }
            }
        }
    }

    /**
     * @param  string  $class
     * @param  string  $action
     * @param  int  $number
     * @param $annotation
     *
     * @return array
     */
    protected function setRouteData(string $class, string $action, int $number, $annotation)
    {
        $routeData = [
            "controller"  => $class,
            "action"      => $action,
            "http_method" => $annotation["Routing"][$number]["method"] ?? "GET",
            "pattern"     => $annotation["Routing"][$number]["url"],
        ];

        if (isset($annotation["Middleware"])) {
            $routeData = array_merge($routeData, ["middleware" => $this->handleAnnotationMiddleware($annotation["Middleware"])]);
        }

        if (isset($annotation["AfterMiddleware"])) {
            $routeData = array_merge($routeData, ["after_middleware" => $this->handleAnnotationMiddleware($annotation["AfterMiddleware"])]);
        }

        return $routeData;
    }

    /**
     * @param  array  $annotation
     *
     * @return array
     */
    protected function handleAnnotationMiddleware(array $annotation): array
    {
        $middleware = [];
        $count      = count($annotation);

        for ($i = 0; $i < $count; $i++) {
            $middleware[$i][] = $annotation[$i]["name"];

            if (isset($annotation[$i]["params"])) {
                $middleware[$i][] = $annotation[$i]["params"];
            }
        }

        return $middleware;
    }

}
