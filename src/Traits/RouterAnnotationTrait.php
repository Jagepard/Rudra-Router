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

trait RouterAnnotationTrait
{
    /**
     * Collects and processes annotations from the specified controllers.
     *
     * This method scans each controller class for Routing and Middleware annotations,
     * builds route definitions based on those annotations, and either:
     * - Registers them directly via `set()` (if $getter = false), or
     * - Returns them as an array (if $getter = true).
     * 
     * @throws \Exception If a specified controller class does not exist.
     */
    public function annotationCollector(array $controllers, bool $getter = false, bool $attributes = false): ?array
    {
        $annotations       = [];
        $annotationService = $this->rudra->get(Annotation::class);

        foreach ($controllers as $controller) {
            if (!class_exists($controller)) {
                throw new \Exception("Remove the $controller controller from the routes.php file.");
            }

            $reflection = new \ReflectionClass($controller);
            $methods    = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $action     = $method->getName();
                $annotation = $attributes 
                    ? $annotationService->getAttributes($controller, $action)
                    : $annotationService->getAnnotations($controller, $action);

                $middleware = [];

                if (isset($annotation['Middleware'])) {
                    $middleware['before'] = $this->handleAnnotationMiddleware($annotation['Middleware']);
                }

                if (isset($annotation['AfterMiddleware'])) {
                    $middleware['after'] = $this->handleAnnotationMiddleware($annotation['AfterMiddleware']);
                }

                if (isset($annotation['Routing'])) {
                    foreach ($annotation['Routing'] as $route) {
                        $route += [
                            'controller' => $controller,
                            'action'     => $action,
                            'middleware' => $middleware,
                            'method'     => 'GET',
                        ];

                        // Determine the key used for the URL (supports both 'url' and legacy '0' index)
                        $urlKey  = array_key_exists('url', $route) ? 'url' : (array_key_exists(0, $route) ? 0 : 'url');
                        $rawUrls = $route[$urlKey] ?? '';
                        $urls    = is_array($rawUrls) ? $rawUrls : [$rawUrls];
                        $expandedUrls = [];

                        foreach ($urls as $url) {
                            $expandedUrls = array_merge($expandedUrls, $this->expandOptionalSegments((string)$url));
                        }

                        // Register each expanded URL while preserving all other route parameters (method, middleware, etc.)
                        foreach (array_unique($expandedUrls) as $expandedUrl) {
                            $currentRoute = $route;
                            $currentRoute[$urlKey] = $expandedUrl;

                            $getter 
                                ? $annotations[] = [$currentRoute] : $this->set($currentRoute);
                        }
                    }
                }
            }
        }

        return $getter ? $annotations : null;
    }

    /**
     * Expands optional route segments enclosed in square brackets.
     * Ignores brackets that are part of regex parameters (after ':').
     *
     * Example: 'admin[/item[/page[/:page]]]' expands to 4 routes:
     * ['admin', 'admin/item', 'admin/item/page', 'admin/item/page/:page']
     */
    protected function expandOptionalSegments(string $url): array
    {
        // Find '[' that is NOT part of a regex parameter (not immediately after ':')
        if (!preg_match('/(?<!:)\[([^\[\]]*)\]/', $url, $matches, PREG_OFFSET_CAPTURE)) {
            return [$url];
        }
        
        $start  = $matches[0][1];
        $length = strlen($matches[0][0]);
        $inner  = $matches[1][0];
        $prefix = substr($url, 0, $start);
        $suffix = substr($url, $start + $length);
        
        return array_unique(array_merge(
            $this->expandOptionalSegments($prefix . $suffix),
            $this->expandOptionalSegments($prefix . $inner . $suffix)
        ));
    }

    /**
     * Processes middleware annotations into a valid middleware format.
     * #[Middleware(name: 'Auth', params: 'admin')] to: ['Auth', 'admin']
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
