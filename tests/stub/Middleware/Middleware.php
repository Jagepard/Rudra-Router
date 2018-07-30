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

    public function __invoke()
    {
       $this->container()->set('middleware', 'middleware', 'raw');
    }
}