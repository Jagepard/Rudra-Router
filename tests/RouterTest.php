<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Facades\Rudra, Interfaces\RudraInterface};
use Rudra\Annotation\Annotation;
use Rudra\Router\Router;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Router\Tests\Stub\Middleware\Middleware;

class RouterTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Rudra::binding()->set([RudraInterface::class => Rudra::run()]);
        Rudra::set(["annotation", Annotation::class]);
        Rudra::set(["router", Router::class]);

        Rudra::get("router")->setNamespace("Rudra\\Router\\Tests\\Stub\\");
    }

    public function testSetNamespace()
    {
        $this->setContainer();
        Rudra::get("router")->setNamespace(RudraInterface::class);
        $class    = new \ReflectionClass(Rudra::get("router"));
        $property = $class->getProperty("namespace");
        $property->setAccessible(true);

        $this->assertEquals(
            RudraInterface::class, $property->getValue(Rudra::get("router"))
        );
    }

    public function testMiddlewareTrait()
    {
        $this->setContainer();
        $controller = new MainController(Rudra::run());
        $controller->middleware([["Middleware", ["int" => 123]], ["Middleware", ["int" => 125]]]);

        $this->assertEquals(Middleware::class, Rudra::config()->get("middleware"));
    }
}
