<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Interfaces\ApplicationInterface;

trait RouterMiddlewareTrait
{
    public function middleware($middleware): void
    {
        $this->application()->objects()->get("router")->handleMiddleware($middleware);
    }

    abstract public function application(): ApplicationInterface;
}
