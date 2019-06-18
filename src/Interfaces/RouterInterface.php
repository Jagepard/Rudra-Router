<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Interfaces;

interface RouterInterface
{
    /**
     * RouterInterface constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container);

    /**
     * @param array $route
     * @return mixed
     */
    public function set(array $route);

    /**
     * @param string $class
     * @param string $method
     * @param int    $number
     */
    public function annotation(string $class, string $method, int $number = 0): void;

    /**
     * @param array $classAndMethod
     * @param null  $params
     */
    public function directCall(array $classAndMethod, $params = null): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function get(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function post(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function put(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function patch(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function delete(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param        $target
     */
    public function any(string $pattern, $target): void;

    /**
     * @param string $pattern
     * @param string $controller
     * @param array  $actions
     */
    public function resource(string $pattern, string $controller, array $actions = []): void;
}
