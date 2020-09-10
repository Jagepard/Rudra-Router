<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Tests;

use Rudra\Container\{Application, Interfaces\ApplicationInterface};
use Rudra\Annotation\Annotation;
use Rudra\Exceptions\RouterException;
use Rudra\Router\Router;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterMethodTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Application::$application = null;
        Application::run()->binding()->set([ApplicationInterface::class => Application::run()]);
        Application::run()->objects()->set(["annotation", Annotation::class]);
        Application::run()->objects()->set(["router", Router::class]);
        Application::run()->objects()->get("router")->setNamespace("Rudra\\Router\\Tests\\Stub\\");
    }

    protected function setRouteEnvironment(string $requestUri, string $requestMethod, string $pattern, string $controller = 'MainController'): void
    {
        $_SERVER["REQUEST_URI"]    = $requestUri;
        $_SERVER["REQUEST_METHOD"] = $requestMethod;
        $method                    = strtolower($requestMethod);
        $action                    = "action" . ucfirst($method);
        $this->setContainer();

        Application::run()->objects()->get("router")->$method($pattern, $controller . "::" . $action);
        $this->assertEquals($requestMethod, Application::run()->objects()->get($action));
    }

    public function testGetWithFullQualifiedNamespace()
    {
        $this->setRouteEnvironment(
            "test/page",
            "GET",
            "/test/page",
            "Rudra\Router\Tests\Stub\Controllers\MainController:fq"
        );
    }

    public function testGet(): void
    {
        $this->setRouteEnvironment("test/page?id=98", "GET", "/test/page");
    }

    public function testPost(): void
    {
        $this->setRouteEnvironment("test/page?some=123", "POST", "/test/page");
    }

    public function testPut(): void
    {
        $this->setRouteEnvironment("test/page", 'PUT', "/test/page");
    }

    public function testPatch(): void
    {
        $this->setRouteEnvironment("test/page", 'PATCH', "/test/page");
    }

    public function testDelete(): void
    {
        $this->setRouteEnvironment("test/page", 'DELETE', "/test/page");
    }

    public function testAny(): void
    {
        $_SERVER["REQUEST_URI"]    = "test/page";
        $_SERVER["REQUEST_METHOD"] = "PATCH";
        $this->setContainer();

        Application::run()->objects()->get("router")->any("/test/page", "MainController::actionAny");
        $this->assertEquals("ANY", Application::run()->objects()->get("actionAny"));
    }

    protected function setRouteResourceEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER["REQUEST_URI"]    = "api/123";
        $_SERVER["REQUEST_METHOD"] = $requestMethod;
        $this->setContainer();

        Application::run()->objects()->get("router")->resource("api/{id}", "MainController");
        $this->assertEquals($action, Application::run()->objects()->get($action));
    }

    public function testResource(): void
    {
        $this->setRouteResourceEnvironment("GET", "read");
        $this->setRouteResourceEnvironment("POST", "create");
        $this->setRouteResourceEnvironment("PUT", "update");
        $this->setRouteResourceEnvironment("DELETE", "delete");
    }

    protected function setRouteResourcePostEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER["REQUEST_URI"]    = "api/123";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST["_method"]          = $requestMethod;
        $this->setContainer();

        Application::run()->objects()->get("router")->resource("api/{id}", "MainController");
        $this->assertEquals($action, Application::run()->objects()->get($action));
    }

    public function testResourcePost(): void
    {
        $this->setRouteResourcePostEnvironment("DELETE", "delete");
        $this->setRouteResourcePostEnvironment("PUT", "update");
        $this->setRouteResourcePostEnvironment("PATCH", "update");
    }

    protected function setRoutePostEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER["REQUEST_URI"]    = "api/123";
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST["_method"]          = $requestMethod;
        $this->setContainer();

        $method = strtolower($requestMethod);

        Application::run()->objects()->get("router")->$method("api/{id}", 'MainController' . "::" . $action);
        $this->assertEquals($action, Application::run()->objects()->get($action));
    }

    public function testPostMethods(): void
    {
        $this->setRoutePostEnvironment("DELETE", "delete");
        $this->setRoutePostEnvironment("PUT", "update");
        $this->setRoutePostEnvironment("PATCH", "update");
    }

    public function testMiddleware()
    {
        $_SERVER["REQUEST_URI"]    = "123/456";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $this->setContainer();

        Application::run()->objects()->get("router")->get("123/{id}", "MainController::read",
            ["before" => [["Middleware", ["int" => 1]], ["Middleware", ["int" => 2]]],
             "after"  => [["Middleware", ["int" => 3]], ["Middleware", ["int" => 4]]]]
        );

        $this->assertEquals("middleware", Application::run()->objects()->get("middleware"));
    }

    public function testRouterExceptionWithNamespace()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment(
            "test/page",
            "GET",
            "/test/page",
            "Rudra\Router\Tests\Stub\Controllers\FalseController:fq"
        );
    } // @codeCoverageIgnore

    public function testRouterException()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment("test/page", "GET", "/test/page", "FalseController");
    } // @codeCoverageIgnore

    public function testRouterMethodException()
    {
        $_SERVER["REQUEST_URI"]    = "test/page";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $this->setContainer();

        $this->expectException(RouterException::class);

        Application::run()->objects()->get("router")->get("/test/page", "MainController::actionFalse");
    } // @codeCoverageIgnore

    public function testClosure()
    {
        $_SERVER["REQUEST_URI"]    = "test/page";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $this->setContainer();

        Application::run()->objects()->get("router")->get("/test/page", function () {
            Application::run()->objects()->set(["closure", ["closure", "raw"]]);
        }
        );

        $this->assertEquals("closure", Application::run()->objects()->get("closure"));
    }
}
