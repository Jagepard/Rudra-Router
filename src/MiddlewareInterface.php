<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router;

interface MiddlewareInterface
{
    /**
     * @param  array $chainOfMiddlewares
     * @return void
     */
    public function next(array $chainOfMiddlewares): void;
}
