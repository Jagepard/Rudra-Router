<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Interfaces\RudraInterface;

trait RouterMiddlewareTrait
{
    public function middleware($middleware): void
    {
        $this->rudra()->get("router")->handleMiddleware($middleware);
    }

    abstract public function rudra(): RudraInterface;
}
