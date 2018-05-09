<?php
/**
 * Date: 13.04.18
 * Time: 21:43
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\tests;

use Rudra\Container;
use \stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;


class RouterMiddlewareTraitTest extends PHPUnit_Framework_TestCase
{

    public function testMiddlewareTrait()
    {
        $controller = new MainController();
        $controller->init(Container::app());
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', Container::$app->get('middleware'));
    }
}