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
        $controller = new MainController();
        Container::$app = null;
        Container::app()->setBinding(ContainerInterface::class, Container::$app);
        Container::$app->set(
            'router',
            'Rudra\Router',
            ['Rudra\\Tests\\Stub\\', ['engine' => 'twig']]
        );
        $controller->init(Container::$app);
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', Container::$app->get('middleware'));
    }
}
