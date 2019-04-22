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

    protected function setContainer()
    {
        Container::$app  = null;

        rudra()->setBinding(ContainerInterface::class, rudra());
        rudra()->set('annotation', 'Rudra\Annotation');
        rudra()->set('router', 'Rudra\Router');
        rudra()->get('router')->setNamespace('Rudra\\Tests\\Stub\\');
    }

    public function testAnnotation()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        rudra()->get('router')->annotation('MainController', 'actionIndex');

        $this->assertEquals('actionIndex', rudra()->get('actionIndex'));
    }

    public function testAnnotationCollector()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        rudra()->get('router')->annotationCollector([['MainController', 'actionIndex']]);

        $this->assertEquals('actionIndex', rudra()->get('actionIndex'));
    }

    public function testAnnotationCollectorMultilevel()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->setContainer();
        rudra()->get('router')->annotationCollector(['dashboard' => ['blog' => ['MainController', 'actionIndex']]], true);

        $this->assertEquals('actionIndex', rudra()->get('actionIndex'));
    }
}
