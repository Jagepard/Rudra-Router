<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static void setRequestMethod(array $route)
 * @method static void setNamespace(string $namespace)
 * @method static void annotation(string $class, string $method, int $number = 0)
 * @method static void annotationCollector(array $data)
 * @method static void directCall(array $classAndMethod, $params = null)
 * @method static void get(string $pattern, $target, array $middleware = [])
 * @method static void post(string $pattern, $target, array $middleware = [])
 * @method static void put(string $pattern, $target, array $middleware = [])
 * @method static void patch(string $pattern, $target, array $middleware = [])
 * @method static void delete(string $pattern, $target, array $middleware = [])
 * @method static void any(string $pattern, $target, array $middleware = [])
 * @method static void resource(string $pattern, string $controller, array $actions = [])
 *
 * @see Router
 */
final class RouterFacade
{
    use FacadeTrait;
}
