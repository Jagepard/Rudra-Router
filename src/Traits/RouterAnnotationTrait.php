<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Annotation\Annotation;

trait RouterAnnotationTrait
{

    /**
     * @param  array  $routes
     * @param  string  $defaultAction
     */
    public function annotationCollector(array $routes, string $defaultAction = "actionIndex")
    {
        foreach ($routes as $route) {
            $this->annotation($route[0], $route[1] ?? $defaultAction, $route[2] ?? 0);
        }
    }

    /**
     * @param  string  $controller
     * @param  string  $action
     * @param  int  $line
     */
    public function annotation(string $controller, string $action, int $line = 0): void
    {
        $annotation = $this->rudra()->get(Annotation::class)
            ->getAnnotations($this->setClassName($controller, $this->namespace."Controllers\\"), $action);

        if (isset($annotation["Routing"])) {
            $this->handleRequestMethod($this->setRouteData($controller, $action, $line, $annotation));
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
