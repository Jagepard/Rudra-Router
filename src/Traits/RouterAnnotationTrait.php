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
    /**
     * @param array $controllers
     */
    public function annotationCollector(array $controllers)
    {
        foreach ($controllers as $controller) {
            $methods = get_class_methods($controller);

            foreach ($methods as $method) {
                $annotation = Rudra::get(Annotation::class)->getAnnotations($controller, $method);
                $middleware = [];

                if (isset($annotation["Middleware"])) {
                    $middleware = array_merge($middleware, ['before' => $this->handleAnnotationMiddleware($annotation["Middleware"])]);
                }

                if (isset($annotation["AfterMiddleware"])) {
                    $middleware = array_merge($middleware, ['after' => $this->handleAnnotationMiddleware($annotation["AfterMiddleware"])]);
                }

                if (isset($annotation["Routing"])) {
                    foreach ($annotation["Routing"] as $route) {
                        $this->set([$route['url'], $route["method"] ?? "GET", [$controller, $method], $middleware]);
                    }
                }
            }
        }
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
            $middleware[$i][] = $annotation[$i]["name"];

            if (isset($annotation[$i]["params"])) {
                $middleware[$i][] = $annotation[$i]["params"];
            }
        }

        return $middleware;
    }
}
