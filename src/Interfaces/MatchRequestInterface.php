<?php

declare(strict_types = 1);

/**
 * Date: 25.04.17
 * Time: 12:37
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Interfaces;
use Rudra\ContainerInterface;
use Rudra\Router;

/**
 * Interface MatchRequestInterface
 *
 * @package Rudra
 */
interface MatchRequestInterface
{

    /**
     * MatchRequestInterface constructor.
     *
     * @param ContainerInterface $container
     * @param Router             $router
     */
    public function __construct(ContainerInterface $container, Router $router);

    /**
     * @param array $route
     *
     * @return bool|void
     */
    public function matchRequest(array $route);


    /**
     * @return bool
     */
    public function isToken(): bool;
}
