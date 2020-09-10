<?php

namespace Rudra\Router\Tests\Stub\Middleware;

use Rudra\Container\Application;

class Middleware
{
    public function __invoke()
    {
        Application::run()->objects()->set(["middleware", ["middleware", "raw"]]);
    }
}
