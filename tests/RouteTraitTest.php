<?php

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Tests;

use Rudra\Container;
use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;
use Rudra\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class RouteTraitTest
 * @package Rudra\tests
 */
class RouteTraitTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var MainController
     */
    protected $controller;

    protected function setUp()
    {
        $this->container = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'stub\\']);
        $this->container->setConfig(['namespaces' => ['web' => 123456]]);

        $this->controller = new MainController();
        $this->controller->init($this->container);
    }

    public function testRoute()
    {
        $this->assertFalse($this->getController()->run());
    }

    public function testHandleException()
    {
        $this->expectException(RouterException::class);
        $this->getController()->exceptionRoute();
    } // @codeCoverageIgnore

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }
}
