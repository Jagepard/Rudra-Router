<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Router\Router;

trait RouterMiddlewareTrait
{

    /**
     * @param $middleware
     * @param  bool  $fullName
     */
    public function middleware($middleware, $fullName = false): void
    {
        $this->rudra()->get(Router::class)->handleMiddleware($middleware, $fullName);
    }

    /**
     * @return \Rudra\Container\Interfaces\RudraInterface
     */
    abstract public function rudra(): RudraInterface;

}
