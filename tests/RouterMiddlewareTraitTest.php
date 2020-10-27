<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Interfaces\RudraInterface, Rudra as R, Facades\Rudra};
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Router\Tests\Stub\Middleware\Middleware;

class RouterMiddlewareTraitTest extends PHPUnit_Framework_TestCase
{
    public function testMiddlewareTrait()
    {
        R::$rudra = null;
        Rudra::binding()->set([RudraInterface::class => Rudra::run()]);
        Rudra::set([Router::class, Router::class]);
        Rudra::get(Router::class)->setNamespace("Rudra\\Router\\Tests\\Stub\\");

        $controller = new MainController(Rudra::run());
        $controller->middleware([["Middleware", ["int" => 123]], ["Middleware", ["int" => 125]]]);

        $this->assertEquals(Middleware::class, Rudra::config()->get("middleware"));
    }
}
