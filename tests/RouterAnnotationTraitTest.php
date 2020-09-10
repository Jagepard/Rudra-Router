<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Application, Interfaces\ApplicationInterface};
use Rudra\Annotation\Annotation;
use Rudra\Router\Router;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterAnnotationTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Application::run()->binding()->set([ApplicationInterface::class => Application::run()]);
        Application::run()->objects()->set(["annotation", Annotation::class]);
        Application::run()->objects()->set(["router", Router::class]);
        Application::run()->objects()->get("router")->setNamespace("Rudra\\Router\\Tests\\Stub\\");
    }

    public function testAnnotation()
    {
        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        $this->setContainer();
        Application::run()->objects()->get("router")->annotation("MainController", "actionIndex");
        $this->assertEquals("actionIndex", Application::run()->objects()->get("actionIndex"));
    }

    public function testAnnotationCollector()
    {
        $_SERVER['REQUEST_URI']    = "test/123";
        $_SERVER['REQUEST_METHOD'] = "GET";

        $this->setContainer();
        Application::run()->objects()->get("router")->annotationCollector([["MainController", "actionIndex"]]);

        $this->assertEquals('actionIndex', Application::run()->objects()->get("actionIndex"));
    }

    public function testAnnotationCollectorMultilevel()
    {
        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        $this->setContainer();
        Application::run()->objects()->get("router")->annotationCollector(["dashboard" => ["blog" => ["MainController", "actionIndex"]]], true);

        $this->assertEquals("actionIndex", Application::run()->objects()->get("actionIndex"));
    }
}
