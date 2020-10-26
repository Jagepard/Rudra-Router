<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Facades\Rudra, Interfaces\RudraInterface};
use Rudra\Exceptions\RouterException;
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouteTraitTest extends PHPUnit_Framework_TestCase
{
    protected MainController $controller;

    protected function setUp(): void
    {
        Rudra::binding([RudraInterface::class => Rudra::run()]);
        Rudra::services(["router" => [Router::class, "stub\\"]]);
        Rudra::config(["namespaces" => ["web" => 123456]]);

        $this->controller = new MainController(Rudra::run());
        $this->controller->init();
    }

    public function testHandleException()
    {
        $this->expectException(RouterException::class);
        $this->controller->exceptionRoute();
    } // @codeCoverageIgnore
}
