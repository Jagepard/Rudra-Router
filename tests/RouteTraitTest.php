<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Tests;

use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;
use Rudra\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

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
