<?php

/**
 * Date: 21.04.17
 * Time: 19:24
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterMiddlewareTrait
 *
 * @package Rudra
 */
trait RouterMiddlewareTrait
{

    /**
     * @param $middleware
     */
    public function middleware($middleware): void
    {
        $this->container()->get('router')->handleMiddleware($middleware);
    }

    /**
     * @return ContainerInterface
     */
    protected abstract function container(): ContainerInterface;
}
