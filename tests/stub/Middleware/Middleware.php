<?php

namespace Rudra\Tests\Stub\Middleware;

/**
 * Class Middleware
 * @package Rudra\Tests\Stub\Middleware
 */
class Middleware
{

    public function __invoke()
    {
        rudra()->set('middleware', 'middleware', 'raw');
    }
}