<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Application, Interfaces\ApplicationInterface};
use Rudra\Exceptions\RouterException;
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouteTraitTest extends PHPUnit_Framework_TestCase
{
    protected MainController $controller;

    protected function setUp(): void
    {
        Application::run()->binding()->set([ApplicationInterface::class => Application::run()]);
        Application::run()->objects()->set(["router", [Router::class, "stub\\"]]);
        Application::run()->config()->set(["namespaces" => ["web" => 123456]]);

        $this->controller = new MainController(Application::run());
        $this->controller->init();
    }

    public function testHandleException()
    {
        $this->expectException(RouterException::class);
        $this->controller->exceptionRoute();
    } // @codeCoverageIgnore
}
