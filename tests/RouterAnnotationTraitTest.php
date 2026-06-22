<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 * 
 * phpunit src/tests/RouterAnnotationTraitTest --coverage-html src/tests/coverage-html
 */

namespace Rudra\Router\Tests;

use Rudra\Annotation\Annotation;
use Rudra\Container\Facades\Rudra;
use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Router\Router as Rtr;
use Rudra\Router\RouterFacade as Router;

class RouterAnnotationTraitTest extends \PHPUnit\Framework\TestCase
{
   protected function setContainer()
   {
       Rudra::binding([RudraInterface::class => Rudra::run()]);
       Rudra::services(["router" => [Rtr::class, "stub\\"]]);
       Rudra::set([Annotation::class, Annotation::class]);
       Router::setNamespace("Rudra\\Router\\Tests\\Stub\\");
   }

   public function testAnnotation()
   {
       $_SERVER["REQUEST_URI"]    = "test/123";
       $_SERVER["REQUEST_METHOD"] = "GET";

       $this->setContainer();
       Router::annotation("MainController", "actionIndex");
       $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
   }

//    public function testAnnotationCollector()
//    {
//        $_SERVER["REQUEST_URI"]    = "test/123";
//        $_SERVER["REQUEST_METHOD"] = "GET";

//        $this->setContainer();
//        Router::annotationCollector([["MainController", "actionIndex"]]);

//        $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
//    }

//    public function testAnnotationCollectorMultilevel()
//    {
//        $_SERVER["REQUEST_URI"]    = "test/123";
//        $_SERVER["REQUEST_METHOD"] = "GET";

//        $this->setContainer();
//        Router::annotationCollector(["blog" => ["MainController", "actionIndex"]]);

//        $this->assertEquals("actionIndex", Rudra::config()->get("actionIndex"));
//    }
}
