<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */

use Rudra\Container;
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
        Container::$app  = null;
        $this->container = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('annotation', 'Rudra\Annotation');
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'Rudra\\Tests\\Stub\\']);
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
        $controller = new MainController();
        $controller->init(Container::app());
        $controller->middleware([['Middleware', ['int' => 123]], ['Middleware', ['int' => 125]]]);

        $this->assertEquals('middleware', $this->container()->get('middleware'));
    }

    /**
     * @return mixed
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}
