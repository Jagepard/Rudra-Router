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
        Container::$app = null;
        $this->container           = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('annotation', 'Rudra\Annotations');
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'stub\\', 'templateEngine' => 'twig']);
    }

    public function testGet(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->get([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'stub\\MainController::namespace',
                'method'     => 'actionGet'
            ]
        );

        $this->assertEquals('GET', $this->container()->get('actionGet'));
    }

    public function testPost(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page?some=123';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->setContainer();

        $this->container()->get('router')->post([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionPost'
            ]
        );

        $this->assertEquals('POST', $this->container()->get('actionPost'));
    }

    public function testPut(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->setContainer();

        $this->container()->get('router')->put([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionPut'
            ]
        );

        $this->assertEquals('PUT', $this->container()->get('actionPut'));
    }

    public function testPatch(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->setContainer();

        $this->container()->get('router')->patch([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionPatch'
            ]
        );

        $this->assertEquals('PATCH', $this->container()->get('actionPatch'));
    }

    public function testDelete(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->setContainer();

        $this->container()->get('router')->delete([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionDelete'
            ]
        );

        $this->assertEquals('DELETE', $this->container()->get('actionDelete'));
    }

    public function testAny(): void
    {
        $_SERVER['REQUEST_URI']    = 'test/page';
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->setContainer();

        $this->container()->get('router')->any([
                'name'       => 'main_page',
                'pattern'    => '/test/page',
                'controller' => 'MainController',
                'method'     => 'actionAny'
            ]
        );

        $this->assertEquals('ANY', $this->container()->get('actionAny'));
    }

    public function testMatchFalse()
    {
        $_SERVER['REQUEST_URI']    = 'test/false';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setContainer();

        $this->container()->get('router')->get([
                'name'       => 'main_page',
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
