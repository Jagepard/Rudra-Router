<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\ExternalTraits;

trait RouterMiddlewareTrait
{
    /**
     * @param $middleware
     */
    public function middleware($middleware): void
    {
        rudra()->get('router')->handleMiddleware($middleware);
    }
}
