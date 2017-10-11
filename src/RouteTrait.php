<?php

declare(strict_types = 1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2017, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

trait RouteTrait
{

    /**
     * @param string $namespace
     * @param string $bundleName
     *
     * @return bool
     */
    protected function route(string $namespace, string $bundleName): bool
    {
        $this->container()->setBinding(Router::class, $this->container()->get('router'));
        $route = $this->container()->new($namespace, ['namespace' => $this->container()->config('namespaces', $bundleName)]);;

        return $route;
    }

    /**
     * @param Router $router
     *
     * @throws RouterException
     */
    protected function handleException(Router $router)
    {
        if (!$router->isToken()) {
            throw new RouterException('404');
        }
    }
}