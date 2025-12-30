<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router\Traits;

use Rudra\Annotation\Annotation;
use Rudra\Container\Interfaces\RudraInterface;

trait RouterAnnotationTrait
{
    /**
     * Collects and processes annotations from the specified controllers.
     *
     * This method scans each controller class for Routing and Middleware annotations,
     * builds route definitions based on those annotations, and either:
     * - Registers them directly via `set()` (if $getter = false), or
     * - Returns them as an array (if $getter = true).
     * --------------------
     * Собирает и обрабатывает аннотации указанных контроллеров.
     *
     * Метод сканирует каждый контроллер на наличие аннотаций Routing и Middleware,
     * формирует определения маршрутов и либо:
     * - Регистрирует их напрямую через `set()` (если $getter = false),
     * - Возвращает как массив (если $getter = true).
     *
     * @param array $controllers
     * @param bool  $getter
     * @param bool  $attributes
     * @return array|null
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
     * Processes middleware annotations into a valid middleware format.
     * --------------------
     * Обрабатывает аннотации middleware в поддерживаемый формат.
     *
     * ```#[Middleware(name: "Auth", params: "admin")]```
     * в:
     * ```['Auth', 'admin']```
     *
     * @param array $annotation
     * @return array
     */
    protected function handleAnnotationMiddleware(array $annotation): array
    {
        $output = [];

        foreach ($annotation as $middleware) {
            if (!isset($middleware['params'])) {
                $output[] = $middleware['name'];
            } else {
                $output[] = [$middleware['name'], $middleware['params']];
            }
        }

        return $output;
    }
}
