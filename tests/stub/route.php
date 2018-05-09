<?php

namespace Rudra\Tests\Stub;

use Rudra\Router;

class Route
{
    public function run(Router $router, $namespace)
    {
        $router->setNamespace($namespace);

        // Routes

        return false;
    }
}
