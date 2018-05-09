<?php

namespace Rudra\Tests\Stub\Middleware;

use Rudra\ExternalTraits\SetContainerTrait;

/**
 * Class Middleware
 * @package Rudra\Tests\Stub\Middleware
 */
class Middleware
{

    use SetContainerTrait;

    /**
     * @param $current
     * @param $middleware
     */
    public function __invoke($current, $middleware)
    {
        // StartMiddleware

       $this->container()->set('middleware', 'middleware', 'raw');

        // EndMiddleware

        $this->next($middleware);
    }

    /**
     * @param $middleware
     */
    protected function next($middleware)
    {
        $this->container()->get('router')->handleMiddleware($middleware);
    }
}