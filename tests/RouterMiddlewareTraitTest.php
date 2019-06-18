<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\tests;

use Rudra\Container;
use Rudra\Interfaces\ContainerInterface;
use Rudra\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class RouterMiddlewareTraitTest extends PHPUnit_Framework_TestCase
{
    public function testMiddlewareTrait()
    {
        Container::$app = null;
        rudra()->setBinding(ContainerInterface::class, rudra());
        rudra()->set('router', 'Rudra\Router');
        rudra()->get('router')->setNamespace('Rudra\\Tests\\Stub\\');

        $controller = new MainController(rudra());
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', rudra()->get('middleware'));
    }
}
