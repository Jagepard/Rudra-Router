<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Interfaces;

/**
 * Interface RouterInterface
 * @package Rudra\Interfaces
 */
interface RouterInterface
{

    /**
     * RouterInterface constructor.
     * @param ContainerInterface $container
     * @param string             $namespace
     */
    public function __construct(ContainerInterface $container, string $namespace);

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
     * @param array $route
     */
    public function get(array $route): void;

    /**
     * @param array $route
     */
    public function post(array $route): void;

    /**
     * @param array $route
     */
    public function put(array $route): void;

    /**
     * @param array $route
     */
    public function patch(array $route): void;

    /**
     * @param array $route
     */
    public function delete(array $route): void;

    /**
     * @param array $route
     */
    public function any(array $route): void;

    /**
     * @param array $route
     */
    public function resource(array $route): void;
}
