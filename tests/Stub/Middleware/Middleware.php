<?php

namespace Rudra\Router\Tests\Stub\Middleware;

use Rudra\Container\Facades\Rudra;

class Middleware
{
    public function __invoke()
    {
        Rudra::config()->set(["middleware" => Middleware::class]);
    }
}
