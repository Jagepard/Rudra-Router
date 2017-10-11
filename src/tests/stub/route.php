<?php

namespace stub\Route;

use Rudra\Router;

class Route
{
    public function run(Router $router, $namespace)
    {
        $router->setNamespace($namespace);

        // Routes

        return $router->isToken();
    }
}
