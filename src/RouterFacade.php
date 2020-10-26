<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static void set(array $route)
 * @method static void annotation(string $class, string $method, int $number = 0)
 * @method static void directCall(array $classAndMethod, $params = null)
 * @method static void get(string $pattern, $target)
 * @method static void post(string $pattern, $target)
 * @method static void put(string $pattern, $target)
 * @method static void patch(string $pattern, $target)
 * @method static void delete(string $pattern, $target)
 * @method static void any(string $pattern, $target)
 * @method static void resource(string $pattern, string $controller, array $actions = [])
 *
 * @see Router
 */
final class RouterFacade
{
    use FacadeTrait;
}
