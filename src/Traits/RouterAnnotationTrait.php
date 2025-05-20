<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Annotation\Annotation;
use Rudra\Container\Interfaces\RudraInterface;

trait RouterAnnotationTrait
{
    /**
     * @param  array   $controllers
     * @param  boolean $getter
     * @param  boolean $attributes
     * @return void
     */
    public function annotationCollector(array $controllers, bool $getter = false, bool $attributes = false): ?array
    {
        $annotations       = [];
        $annotationService = $this->rudra->get(Annotation::class);

        foreach ($controllers as $controller) {
            if (!class_exists($controller)) {
                throw new \Exception("Удалите контроллер $controller из файла routes.php");
            }

            $reflection = new \ReflectionClass($controller);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $action     = $method->getName();
                $annotation = $attributes 
                    ? $annotationService->getAttributes($controller, $action)
                    : $annotationService->getAnnotations($controller, $action);

                $middleware = [];

                if (isset($annotation["Middleware"])) {
                    $middleware['before'] = $this->handleAnnotationMiddleware($annotation["Middleware"]);
                }

                if (isset($annotation["AfterMiddleware"])) {
                    $middleware['after'] = $this->handleAnnotationMiddleware($annotation["AfterMiddleware"]);
                }

                if (isset($annotation["Routing"])) {
                    foreach ($annotation["Routing"] as $route) {
                        $route += [
                            'controller' => $controller,
                            'action'     => $action,
                            'middleware' => $middleware,
                            'method'     => 'GET',
                        ];

                        $getter
                            ? $annotations[] = [$route]
                            : $this->set($route);
                    }
                }
            }
        }

        return $getter ? $annotations : null;
    }

    /**
     * @param array $annotation
     * @return array
     */
    protected function handleAnnotationMiddleware(array $annotation): array
    {
        $middleware = [];

        foreach ($annotation as $item) {
            $entry = [$item['name']];
            if (isset($item['params'])) {
                $entry[] = $item['params'];
            }
            $middleware[] = $entry;
        }

        return $middleware;
    }
}
