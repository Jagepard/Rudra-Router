<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Exceptions\RouterException;

interface RouterInterface
{
    public function set(array $route): void;
    public function directCall(array $route, $params = null): void;
}
