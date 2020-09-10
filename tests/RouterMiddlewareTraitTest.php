<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Application, Interfaces\ApplicationInterface};
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterMiddlewareTraitTest extends PHPUnit_Framework_TestCase
{
    public function testMiddlewareTrait()
    {
        Application::$application = null;
        Application::run()->binding()->set([ApplicationInterface::class => Application::run()]);
        Application::run()->objects()->set(["router", Router::class]);
        Application::run()->objects()->get("router")->setNamespace("Rudra\\Router\\Tests\\Stub\\");

        $controller = new MainController(Application::run());
        $controller->middleware([["Middleware", ["int" => 123]], ["Middleware", ["int" => 125]]]);

        $this->assertEquals("middleware", Application::run()->objects()->get("middleware"));
    }
}
