<?php

declare(strict_types = 1);

/**
 * Date: 25.04.17
 * Time: 11:15
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Router\Interfaces;


use Rudra\Container\ContainerInterface;
use Rudra\Router\MatchRequest;


/**
 * Interface MatchHttpMethodInterface
 *
 * @package Rudra
 */
interface MatchHttpMethodInterface
{

    /**
     * MatchHttpMethodInterface constructor.
     *
     * @param \Rudra\Container\ContainerInterface $container
     * @param MatchRequest                        $matchRequest
     */
    public function __construct(ContainerInterface $container, MatchRequest $matchRequest);

    /**
     * @param null $route
     *
     * @return mixed
     */
    public function matchRequest($route = null);

    /**
     * @param array $route
     */
    public function matchHttpMethod(array $route): void;


    /**
     * @return ContainerInterface
     */
    public function container(): ContainerInterface;
}
