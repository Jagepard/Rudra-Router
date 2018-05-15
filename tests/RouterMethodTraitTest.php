<?php

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\tests;

use Rudra\Container;
use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class RouterMethodTraitTest
 * @package Rudra\tests
 */
class RouterMethodTraitTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setContainer()
    {
        Container::$app  = null;
        $this->container = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('annotation', 'Rudra\Annotations');
        $this->container->set(
            'router',
            'Rudra\Router',
            ['namespace' => 'Rudra\\Tests\\Stub\\', 'templateEngine' => ['engine' => 'twig']]
        );
    }

    /**
     * @param string $requestUri
     * @param string $requestMethod
     * @param string $pattern
     * @param string $controller
     */
    protected function setRouteEnvironment(
        string $requestUri, string $requestMethod, string $pattern, string $controller = 'MainController'): void
    {
        $_SERVER['REQUEST_URI']    = $requestUri;
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $method                    = strtolower($requestMethod);
        $action                    = 'action' . ucfirst($method);
        $this->setContainer();

        $this->container->get('router')->$method($pattern, $controller . '::' . $action);
        $this->assertEquals($requestMethod, $this->container->get($action));
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

        $this->container->get('router')->any('/test/page','MainController::actionAny');
        $this->assertEquals('ANY', $this->container->get('actionAny'));
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

        $this->container->get('router')->resource('api/{id}', 'MainController');
        $this->assertEquals($action, $this->container->get($action));
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

        $this->container->get('router')->resource('api/{id}', 'MainController');
        $this->assertEquals($action, $this->container->get($action));
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

        $this->container->get('router')->$method('api/{id}', 'MainController' . '::' . $action);
        $this->assertEquals($action, $this->container->get($action));
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

        $this->container->get('router')->middleware('get', '123/{id}', 'MainController::read',
            ['before' => [['Middleware', ['int' => 111111]], ['Middleware', ['int' => 222222]]]]
        );

        $this->assertEquals('middleware', $this->container->get('middleware'));
    }

    public function testRouterExceptionWithNamespace()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment(
            'test/page',
            'GET',
            '/test/page',
            'stub\Controllers\FalseController::fq'
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

        $this->container->get('router')->get('/test/page', 'MainController::actionFalse');
    } // @codeCoverageIgnore

    public function testClosure()
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container->get('router')->get('/test/page', function () {
                $this->container->set('closure', 'closure', 'raw');
            }
        );

        $this->assertEquals('closure', $this->container->get('closure'));
    }
}
