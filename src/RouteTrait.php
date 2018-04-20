<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2017, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

trait RouteTrait
{

    /**
     * @param string $route
     * @param string $bundle
     *
     * @return mixed
     */
    protected function route(string $route, string $bundle)
    {
        return $this->container()->new($route)->run($this->container()->get('router'), $this->container()->config('namespaces', $bundle));
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException('404');
    } // @codeCoverageIgnore

    /**
     * @return ContainerInterface
     */
    public abstract function container(): ContainerInterface;
}
