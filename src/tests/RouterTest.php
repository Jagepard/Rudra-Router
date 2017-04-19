<?php

declare(strict_types = 1);

/**
 * Date: 17.02.17
 * Time: 13:23
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */


use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container;
use Rudra\ContainerInterface;
use Rudra\Router;
use Rudra\RouterException;

/**
 * Class RouterTest
 */
class RouterTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $app;

    protected function setContainer()
    {
        Container::$app  = null;
        $this->container = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('annotation', 'Rudra\Annotations');
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'stub\\', 'templateEngine' => 'twig']);
    }

    public function testAnnotation()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->annotation('MainController', 'actionIndex');
        $this->assertEquals('actionIndex', $this->container()->get('actionIndex'));
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
        $this->setContainer();

        $method = strtolower($requestMethod);
        $action = 'action' . ucfirst($method);

        $this->container()->get('router')->$method([
                'pattern'    => $pattern,
                'controller' => $controller,
                'method'     => $action
            ]
        );

        $this->assertEquals($requestMethod, $this->container()->get($action));
    }

    public function testGet(): void
    {
        $this->setRouteEnvironment('test/page', 'GET', '/test/page', 'stub\Controllers\MainController::namespace');
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

        $this->container()->get('router')->any([
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionAny'
            ]
        );

        $this->assertEquals('ANY', $this->container()->get('actionAny'));
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

        $this->container()->get('router')->resource([
                'pattern'    => 'api/{id}',
                'controller' => 'MainController',
            ]
        );

        $this->assertEquals($action, $this->container()->get($action));
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

        $this->container()->get('router')->resource([
                'pattern'    => 'api/{id}',
                'controller' => 'MainController',
            ]
        );

        $this->assertEquals($action, $this->container()->get($action));
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

        $this->container()->get('router')->$method([
                'pattern'    => 'api/{id}',
                'controller' => 'MainController',
                'method'     => $action
            ]
        );

        $this->assertEquals($action, $this->container()->get($action));
    }

    public function testPostMethods(): void
    {
        $this->setRoutePostEnvironment('DELETE', 'delete');
        $this->setRoutePostEnvironment('PUT', 'update');
        $this->setRoutePostEnvironment('PATCH', 'update');
    }

    public function testMatchFalse()
    {
        $_SERVER['REQUEST_URI']    = 'test/false';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->get([
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionGet'
            ]
        );

        $this->assertEquals(false, $this->container()->get('router')->isToken());
    }

    public function testMiddleware()
    {
        $_SERVER['REQUEST_URI']    = '123/456';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->middleware('get', [
            'pattern'     => '123/{id}',
            'controller'  => 'MainController',
            'method'      => 'read',
            'middleware'  => [['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]
        ]);

        $this->assertEquals('middleware', $this->container()->get('middleware'));
    }

    public function testClosure()
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->get([
                'pattern' => '/test/page',
                'method'  => function () {
                    $this->container()->set('closure', 'closure', 'raw');
                }
            ]
        );

        $this->assertEquals('closure', $this->container()->get('closure'));
    }

    public function testRouterExceptionWithNamespace()
    {
        $this->expectException(RouterException::class);
        $this->setRouteEnvironment('test/page', 'GET', '/test/page', 'stub\Controllers\FalseController::namespace');
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

        $this->container()->get('router')->get([
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionFalse'
            ]
        );
    } // @codeCoverageIgnore

    /**
     * @return mixed
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}
