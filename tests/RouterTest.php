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

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setContainer()
    {
        $this->container = rudra();
        $this->container()->setBinding(ContainerInterface::class, $this->container());
        $this->container()->set('annotation', 'Rudra\Annotation');
        $this->container()->set('router', 'Rudra\Router', ['Rudra\\Tests\\Stub\\']);
    }

    public function testSetNamespace()
    {
        $this->setContainer();
        $this->container()->get('router')->setNamespace(ContainerInterface::class);
        $class    = new ReflectionClass($this->container()->get('router'));
        $property = $class->getProperty('namespace');
        $property->setAccessible(true);

        $this->assertEquals(ContainerInterface::class, $property->getValue($this->container()->get('router')));
    }

    public function testMiddlewareTrait()
    {
        $this->setContainer();
        $controller = new MainController(Container::app());
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', $this->container()->get('middleware'));
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
