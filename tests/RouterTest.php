<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */

use Rudra\Interfaces\ContainerInterface;
use Rudra\Tests\Stub\Controllers\MainController;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class RouterTest
 */
class RouterTest extends PHPUnit_Framework_TestCase
{

    protected function setContainer()
    {
        rudra()->setBinding(ContainerInterface::class, rudra());
        rudra()->set('annotation', 'Rudra\Annotation');
        rudra()->set('router', 'Rudra\Router');
        rudra()->get('router')->setNamespace('Rudra\\Tests\\Stub\\');
    }

    public function testSetNamespace()
    {
        $this->setContainer();
        rudra()->get('router')->setNamespace(ContainerInterface::class);
        $class    = new ReflectionClass(rudra()->get('router'));
        $property = $class->getProperty('namespace');
        $property->setAccessible(true);

        $this->assertEquals(ContainerInterface::class, $property->getValue(rudra()->get('router')));
    }

    public function testMiddlewareTrait()
    {
        $this->setContainer();
        $controller = new MainController(rudra());
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', rudra()->get('middleware'));
    }
}
