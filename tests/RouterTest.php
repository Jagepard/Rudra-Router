<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Application, Interfaces\ApplicationInterface};
use Rudra\Annotation\Annotation;
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Application::run()->binding()->set([ApplicationInterface::class => Application::run()]);
        Application::run()->objects()->set(["annotation", Annotation::class]);
        Application::run()->objects()->set(["router", Router::class]);
        Application::run()->objects()->get("router")->setNamespace("Rudra\\Router\\Tests\\Stub\\");
    }

    public function testSetNamespace()
    {
        $this->setContainer();
        Application::run()->objects()->get("router")->setNamespace(ApplicationInterface::class);
        $class    = new \ReflectionClass(Application::run()->objects()->get("router"));
        $property = $class->getProperty("namespace");
        $property->setAccessible(true);

        $this->assertEquals(
            ApplicationInterface::class, $property->getValue(Application::run()->objects()->get("router"))
        );
    }

    public function testMiddlewareTrait()
    {
        $this->setContainer();
        $controller = new MainController(Application::run());
        $controller->middleware([["Middleware", ["int" => 123]], ["Middleware", ["int" => 125]]]);

        $this->assertEquals("middleware", Application::run()->objects()->get("middleware"));
    }
}
