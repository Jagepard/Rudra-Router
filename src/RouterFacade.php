<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static void set(array $route)
 * @method static void directCall(array $classAndMethod, $params = null)
 * @method static void get(string $pattern, $target, array $middleware = [])
 * @method static void post(string $pattern, $target, array $middleware = [])
 * @method static void put(string $pattern, $target, array $middleware = [])
 * @method static void patch(string $pattern, $target, array $middleware = [])
 * @method static void delete(string $pattern, $target, array $middleware = [])
 * @method static void any(string $pattern, $target, array $middleware = [])
 * @method static void resource(string $pattern, string $controller, array $actions = [])
 * @method static annotationCollector(array $controllers)
 * @method static handleMiddleware(array $middleware)
 *
 * @see Router
 */
final class RouterFacade
{
    use FacadeTrait;
}
