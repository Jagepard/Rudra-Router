<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\tests;

use Rudra\Container;
use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterMethodTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setContainer()
    {
        Container::$app  = null;

        rudra()->setBinding(ContainerInterface::class, Container::$app);
        rudra()->set('annotation', 'Rudra\Annotation');
        rudra()->set('router', 'Rudra\Router');
        rudra()->get('router')->setNamespace('Rudra\\Tests\\Stub\\');
    }

    /**
     * @param string $requestUri
     * @param string $requestMethod
     * @param string $pattern
     * @param string $controller
     */
    protected function setRouteEnvironment(string $requestUri, string $requestMethod, string $pattern, string $controller = 'MainController'): void
    {
        $_SERVER['REQUEST_URI']    = $requestUri;
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $method                    = strtolower($requestMethod);
        $action                    = 'action' . ucfirst($method);
        $this->setContainer();

        rudra()->get('router')->$method($pattern, $controller . '::' . $action);
        $this->assertEquals($requestMethod, rudra()->get($action));
    }

    public function testGetWithFullQualifiedNamespace()
    {
        $this->setRouteEnvironment(
            'test/page',
            'GET',
            '/test/page',
            'Rudra\Tests\Stub\Controllers\MainController:fq'
        );
    }

    public function testGet(): void
    {
        $this->setRouteEnvironment('test/page?id=98', 'GET', '/test/page');
    }

    public function testPost(): void
    {
        $this->setRouteEnvironment('test/page?some=123', 'POST', '/test/page');
    }

    public function testPut(): void
    {
        $this->setRouteEnvironment('test/page', 'PUT', '/test/page');
    }

    public function testPatch(): void
    {
        $this->setRouteEnvironment('test/page', 'PATCH', '/test/page');
    }

    public function testDelete(): void
    {
        $this->setRouteEnvironment('test/page', 'DELETE', '/test/page');
    }

    public function testAny(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->setContainer();

        rudra()->get('router')->any('/test/page', 'MainController::actionAny');
        $this->assertEquals('ANY', rudra()->get('actionAny'));
    }

    /**
     * @param string $requestMethod
     * @param string $action
     */
    protected function setRouteResourceEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER['REQUEST_URI']    = 'api/123';
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $this->setContainer();

        rudra()->get('router')->resource('api/{id}', 'MainController');
        $this->assertEquals($action, rudra()->get($action));
    }

    public function testResource(): void
    {
        $this->setRouteResourceEnvironment('GET', 'read');
        $this->setRouteResourceEnvironment('POST', 'create');
        $this->setRouteResourceEnvironment('PUT', 'update');
        $this->setRouteResourceEnvironment('DELETE', 'delete');
    }

    /**
     * @param string $requestMethod
     * @param string $action
     */
    protected function setRouteResourcePostEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER['REQUEST_URI']    = 'api/123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method']          = $requestMethod;
        $this->setContainer();

        rudra()->get('router')->resource('api/{id}', 'MainController');
        $this->assertEquals($action, rudra()->get($action));
    }

    public function testResourcePost(): void
    {
        $this->setRouteResourcePostEnvironment('DELETE', 'delete');
        $this->setRouteResourcePostEnvironment('PUT', 'update');
        $this->setRouteResourcePostEnvironment('PATCH', 'update');
    }

    /**-
     * @param string $requestMethod
     * @param string $action
     */
    protected function setRoutePostEnvironment(string $requestMethod, string $action): void
    {
        $_SERVER['REQUEST_URI']    = 'api/123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method']          = $requestMethod;
        $this->setContainer();

        $method = strtolower($requestMethod);

        rudra()->get('router')->$method('api/{id}', 'MainController' . '::' . $action);
        $this->assertEquals($action, rudra()->get($action));
    }

    public function testPostMethods(): void
    {
        $this->setRoutePostEnvironment('DELETE', 'delete');
        $this->setRoutePostEnvironment('PUT', 'update');
        $this->setRoutePostEnvironment('PATCH', 'update');
    }

    public function testMiddleware()
    {
        $_SERVER['REQUEST_URI']    = '123/456';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        rudra()->get('router')->get('123/{id}', 'MainController::read',
            ['before' => [['Middleware', ['int' => 1]], ['Middleware', ['int' => 2]]],
             'after'  => [['Middleware', ['int' => 3]], ['Middleware', ['int' => 4]]]]
        );

        $this->assertEquals('middleware', rudra()->get('middleware'));
    }

    public function testRouterExceptionWithNamespace()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment(
            'test/page',
            'GET',
            '/test/page',
            'Rudra\Tests\Stub\Controllers\FalseController:fq'
        );
    } // @codeCoverageIgnore

    public function testRouterException()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment('test/page', 'GET', '/test/page', 'FalseController');
    } // @codeCoverageIgnore

    public function testRouterMethodException()
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->expectException(RouterException::class);

        rudra()->get('router')->get('/test/page', 'MainController::actionFalse');
    } // @codeCoverageIgnore

    public function testClosure()
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        rudra()->get('router')->get('/test/page', function () {
            rudra()->set('closure', 'closure', 'raw');
        }
        );

        $this->assertEquals('closure', rudra()->get('closure'));
    }
}
