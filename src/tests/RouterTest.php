<?php

declare(strict_types = 1);

/**
 * Date: 17.02.17
 * Time: 13:23
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/ContainerTest --coverage-html src/tests/coverage-html
 */


use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container;
use Rudra\ContainerInterface;
use Rudra\Router;
use Rudra\RouterException;

/**
 * Class RouterTest
 */
class RouterTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $app;

    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI']    = '';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->container           = Container::app();
        $this->container->setBinding(ContainerInterface::class, Container::$app);
        $this->container->set('annotation', 'Rudra\Annotations');
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'stub\\', 'templateEngine' => 'twig']);
    }

    public function testRouter(): void
    {
        $this->assertInstanceOf(Router::class, $this->container()->get('router'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAnnotation(): void
    {
        $this->assertNull($this->container()->get('router')->annotation('MainController', 'actionIndex'));
        $this->assertEquals(123, $this->container()->get('equals'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAnnotationWithNamespace(): void
    {
        $this->assertNull($this->container()->get('router')->annotation('stub\\MainController::namespace', 'actionIndex'));
        $this->assertEquals(123, $this->container()->get('equals'));
    }

    public function testAnnotationException(): void
    {
        $this->expectException(RouterException::class);
        $this->container()->get('router')->annotation('FalseController', 'actionIndex');
    }

    public function testAnnotationExceptionWithNamespace(): void
    {
        $this->expectException(RouterException::class);
        $this->container()->get('router')->annotation('stub\\FalseController::namespace', 'actionIndex');
    }

    /**
     * @return mixed
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}
