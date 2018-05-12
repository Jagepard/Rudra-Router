<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\ExternalTraits;

use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;

/**
 * Trait RouteTrait
 * @package Rudra\ExternalTraits
 */
trait RouteTrait
{

    /**
     * @param string $route
     * @param string $bundle
     * @param array  $params
     * @return mixed
     */
    protected function route(string $route, string $bundle, array $params = [])
    {
        return $this->container()->new($route)->run(
            $this->container()->get('router'),
            $this->container()->config('namespaces', $bundle),
            $params);
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException($this->container(), '404');
    } // @codeCoverageIgnore

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
