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
        $this->container = rudra();
        $this->container()->setBinding(ContainerInterface::class, $this->container());
        $this->container()->set('router', 'Rudra\Router', ['stub\\']);
        $this->container()->setConfig(['namespaces' => ['web' => 123456]]);

        $this->controller = new MainController($this->container());
        $this->controller()->init();
    }

    public function testHandleException()
    {
        $this->expectException(RouterException::class);
        $this->controller()->exceptionRoute();
    } // @codeCoverageIgnore

    public function controller()
    {
        return $this->controller;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
