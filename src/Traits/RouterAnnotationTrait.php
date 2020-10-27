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

    protected function handleAnnotation(array $data): void
    {
        foreach ($data as $item) {
            $this->annotation($item[0], $item[1] ?? "actionIndex", $item[2] ?? 0);
        }
    }

    public function annotation(string $controller, string $action = 'actionIndex', int $number = 0): void
    {
        $className  = $this->setClassName($controller, $this->namespace . "Controllers\\");
        $annotation = $this->rudra()->get(Annotation::class)->getAnnotations($className, $action);

        if (isset($annotation["Routing"])) {
            $httpMethod = $annotation["Routing"][$number]["method"] ?? "GET";
            $routeData  = $this->setRouteData($controller, $action, $number, $annotation, $httpMethod);

            $this->set($routeData);
        }
    }

    protected function setRouteData(string $class, string $method, int $number, $result, $httpMethod)
    {
        $dataRoute = [
            'controller'  => $class,
            'method'      => $method,
            'http_method' => $httpMethod,
            'pattern'     => $result["Routing"][$number]["url"]
        ];

        if (isset($result["Middleware"])) {
            $dataRoute = array_merge($dataRoute, ["middleware" => $this->handleAnnotationMiddleware($result["Middleware"])]);
        }

        if (isset($result["AfterMiddleware"])) {
            $dataRoute = array_merge($dataRoute, ["after_middleware" => $this->handleAnnotationMiddleware($result["AfterMiddleware"])]);
        }

        return $dataRoute;
    }

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

    abstract public function set(array $route);
    abstract protected function setClassName(string $className, string $type): string;
}
