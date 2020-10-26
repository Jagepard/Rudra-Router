<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Container\Interfaces\RudraInterface;

interface RouterInterface
{
    public function set(array $route);
    public function annotation(string $class, string $method, int $number = 0): void;
    public function directCall(array $classAndMethod, $params = null): void;
    public function get(string $pattern, $target): void;
    public function post(string $pattern, $target): void;
    public function put(string $pattern, $target): void;
    public function patch(string $pattern, $target): void;
    public function delete(string $pattern, $target): void;
    public function any(string $pattern, $target): void;
    public function resource(string $pattern, string $controller, array $actions = []): void;
    public function rudra(): RudraInterface;
}
