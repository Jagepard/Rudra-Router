<?php
/**
 * Date: 12.04.18
 * Time: 18:14
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\tests;


use Rudra\Container;
use Rudra\ContainerInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;


/**
 * Class RouterAnnotationTraitTest
 * @package Rudra\tests
 */
class RouterAnnotationTraitTest extends PHPUnit_Framework_TestCase
{

    public function testAnnotation()
    {
        $_SERVER['REQUEST_URI']    = 'test/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        Container::$app = null;
        $container      = Container::app();
        $container->setBinding(ContainerInterface::class, Container::$app);
        $container->set('annotation', 'Rudra\Annotations');
        $container->set('router', 'Rudra\Router', ['namespace' => 'stub\\', 'templateEngine' => ['engine' => 'twig']]);
        $container->get('router')->annotation('MainController', 'actionIndex');

        $this->assertEquals('actionIndex', $container->get('actionIndex'));
    }
}