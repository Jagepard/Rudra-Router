<?php

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\tests;

use Rudra\Container;
use Rudra\Interfaces\ContainerInterface;
use Rudra\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class RouterMiddlewareTraitTest
 * @package Rudra\tests
 */
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
