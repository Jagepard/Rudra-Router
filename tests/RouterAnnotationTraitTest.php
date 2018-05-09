<?php

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\tests;

use Rudra\Container;
use Rudra\Interfaces\ContainerInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class RouterAnnotationTraitTest
 * @package Rudra\tests
 */
class RouterAnnotationTraitTest extends PHPUnit_Framework_TestCase
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
        $this->container->set('annotation', 'Rudra\Annotations');
        $this->container->set('router', 'Rudra\Router', ['namespace' => 'Rudra\\Tests\\Stub\\', 'templateEngine' => ['engine' => 'twig']]);
    }

    public function testAnnotation()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        $this->container->get('router')->annotation('MainController', 'actionIndex');

        $this->assertEquals('actionIndex', $this->container->get('actionIndex'));
    }

    public function testAnnotationCollector()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        $this->container->get('router')->annotationCollector([['MainController', 'actionIndex']]);

        $this->assertEquals('actionIndex', $this->container->get('actionIndex'));
    }

    public function testAnnotationCollectorMultilevel()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        $this->container->get('router')->annotationCollector(['dashboard' => ['blog' => ['MainController', 'actionIndex']]], true);

        $this->assertEquals('actionIndex', $this->container->get('actionIndex'));
    }
}
