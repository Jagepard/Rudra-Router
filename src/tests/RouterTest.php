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
        $this->setRouteEnvironment('test/page', 'GET', '/test/page', 'stub\\MainController::namespace');
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

    /**
     * @return mixed
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}
