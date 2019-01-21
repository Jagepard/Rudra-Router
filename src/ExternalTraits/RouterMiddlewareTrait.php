<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\ExternalTraits;

/**
 * Trait RouterMiddlewareTrait
 * @package Rudra
 */
trait RouterMiddlewareTrait
{

    /**
     * @param $middleware
     */
    public function middleware($middleware): void
    {
        $this->container->get('router')->handleMiddleware($middleware);
    }
}
