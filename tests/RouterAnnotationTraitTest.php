<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Facades\Rudra,Interfaces\RudraInterface};
use Rudra\Annotation\Annotation;
use Rudra\Router\Router as Rtr;
use Rudra\Router\RouterFacade as Router;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterAnnotationTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Rudra::binding([RudraInterface::class => Rudra::run()]);
        Rudra::services(["router" => [Rtr::class, "stub\\"]]);
        Rudra::set(["annotation", Annotation::class]);
        Rudra::set(["router", Rtr::class]);
        Router::setNamespace("Rudra\\Router\\Tests\\Stub\\");
    }

    public function testAnnotation()
    {
        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        $this->setContainer();
        Router::annotation("MainController", "actionIndex");
        $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
    }

    public function testAnnotationCollector()
    {
        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        $this->setContainer();
        Router::annotationCollector([["MainController", "actionIndex"]]);

        $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
    }

    public function testAnnotationCollectorMultilevel()
    {
        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        $this->setContainer();
        Router::annotationCollector(["dashboard" => ["blog" => ["MainController", "actionIndex"]]], true);

        $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
    }
}
