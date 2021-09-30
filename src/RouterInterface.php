<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Exceptions\RouterException;

interface RouterInterface
{
    /**
     * @param  array  $route
     */
    public function set(array $route): void;

    /**
     * @param array $route
     * @param null $params
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void;
}
