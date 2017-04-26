<?php

declare(strict_types = 1);

/**
 * Date: 26.04.17
 * Time: 16:34
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router\Interfaces;


use Rudra\Container\ContainerInterface;


/**
 * Interface RouterInterface
 *
 * @package Rudra\Router\Interfaces
 */
interface RouterInterface
{

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine);

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    public function setRequestMethod(string $param = null);

    /**
     * @param array|null $route
     *
     * @return MatchHttpMethod|void
     */
    public function matchHttpMethod(array $route = null);

    /**
     * @return MatchAnnotationInterface
     */
    public function matchAnnotation(): MatchAnnotationInterface;

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route);

    /**
     * @param array $route
     * @param null  $params
     *
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void;

    /**
     * @param array $middleware
     */
    public function handleMiddleware(array $middleware);

    /**
     * @param string $className
     * @param string $type
     *
     * @return string
     * @throws RouterException
     */
    public function setClassName(string $className, string $type): string;

    /**
     * @param array $route
     * @param       $params
     *
     * @return mixed
     */
    public function setCallable(array $route, $params);
}