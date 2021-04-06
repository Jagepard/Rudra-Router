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

    /**
     * @param  array  $route
     *
     * @return mixed
     */
    public function handleRequestMethod(array $route);

    /**
     * @param  string  $controller
     * @param  string  $action
     * @param  int  $line
     */
    public function annotation(string $controller, string $action, int $line = 0): void;

    /**
     * @param  array  $classAndMethod
     * @param  null  $params
     */
    public function directCall(array $classAndMethod, $params = null): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function get(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function post(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function put(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function patch(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function delete(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param $target
     */
    public function any(string $pattern, $target): void;

    /**
     * @param  string  $pattern
     * @param  string  $controller
     * @param  array  $actions
     */
    public function resource(string $pattern, string $controller, array $actions = []): void;

    /**
     * @return \Rudra\Container\Interfaces\RudraInterface
     */
    public function rudra(): RudraInterface;

}
