<?php

declare(strict_types = 1);

/**
 * Date: 12.04.17
 * Time: 10:55
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router\Interfaces;


use Rudra\Container\ContainerInterface;


/**
 * Interface FacadeInterface
 *
 * @package Rudra\Router\Interfaces
 */
interface FacadeInterface
{

    /**
     * RouterInterface constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine);

    /**
     * @param array $route
     *
     * @return mixed
     */
    public function set(array $route);

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
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

    /**
     * @return bool
     */
    public function isToken(): bool;
}