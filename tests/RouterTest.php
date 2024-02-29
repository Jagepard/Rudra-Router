<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */

namespace Rudra\Router\Tests;

use Rudra\Router\RouterFacade as Router;
use Rudra\Router\Tests\Stub\Middleware\Middleware;
use Rudra\Router\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container\{Rudra as R, Facades\Rudra, Interfaces\RudraInterface};

class RouterTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        R::$rudra = null;
        Rudra::binding()->set([RudraInterface::class => Rudra::run()]);
        Rudra::config()->set(["environment" => "test"]);
    }

    protected function setRouteEnvironment(string $requestUri, string $requestMethod, string $pattern, string $controller = MainController::class): void
    {
        $_SERVER["REQUEST_URI"]    = $requestUri;
        $_SERVER["REQUEST_METHOD"] = $requestMethod;
        $method                    = strtolower($requestMethod);
        $action                    = "action" . ucfirst($method);
        $this->setContainer();

        $route = [
            'url'        => $pattern,
            'method'     => $requestMethod,
            'action'     => "action" . ucfirst($method),
            'controller' => $controller
        ];

        \Rudra\Router\RouterFacade::$method($route);
        $this->assertEquals($requestMethod, Rudra::config()->get($action));
    }

    public function testGetWithFullQualifiedNamespace()
    {
        $this->setRouteEnvironment(
            "test/page",
            "GET",
            "/test/page",
            MainController::class
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

        Router::any([
            'url'        => '/test/page',
            'action'     => 'actionAny',
            'controller' => MainController::class
        ]);
        
        $this->assertEquals("ANY", Rudra::config()->get("actionAny"));
    }

    protected function setRouteResourceEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER["REQUEST_URI"]    = "api/123";
        $_SERVER["REQUEST_METHOD"] = $requestMethod;
        $this->setContainer();

        Router::resource([
            'url' => "api/:id",
            'controller' => MainController::class
        ]);

        $this->assertEquals($action, Rudra::config()->get($action));
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

        Router::resource([
            'url' => "api/:id",
            'controller' => MainController::class
        ]);

        $this->assertEquals($action, Rudra::config()->get($action));
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

        Router::resource([
            'url' => "api/:id",
            'controller' => MainController::class
        ]);

        $this->assertEquals($action, Rudra::config()->get($action));
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

        Router::get([
            'url' => "123/:id",
            'controller' => MainController::class,
            'action' => 'read', 
            'middleware' => [
                "before" => [[Middleware::class]],
                "after"  => [[Middleware::class]]
            ]
        ]);

        $this->assertEquals(Middleware::class, Rudra::config()->get("middleware"));
    }

    public function testClosure()
    {
        $_SERVER["REQUEST_URI"]    = "test/page";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $this->setContainer();

        Rudra::config()->set(["environment" => "test"]);

        Router::get([
            'url' => "test/page",
            'controller' => function () {
                Rudra::config()->set(["closure" => "closure"]);
            }
        ]);

        $this->assertEquals("closure", Rudra::config()->get("closure"));
    }
}
