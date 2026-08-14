<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router\Tests;

use PHPUnit\Framework\TestCase;
use Rudra\Annotation\Annotation;
use Rudra\Container\Facades\Rudra;
use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Router\Router;
use Rudra\Router\Attributes\Routing;
use Rudra\Router\Tests\Stub\Controllers\AnnotatedController;
use Rudra\Router\Tests\Stub\Controllers\MainController;

class RouterAnnotationTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER["REQUEST_URI"]    = "test/123";
        $_SERVER["REQUEST_METHOD"] = "GET";

        Rudra::binding([RudraInterface::class => Rudra::run()]);
        Rudra::set([Annotation::class, Annotation::class]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"]);
    }

    private function getRouter(): Router
    {
        return new Router(Rudra::run());
    }

    private function flattenRoutes(array $routes): array
    {
        return array_map(fn(array $wrapped) => $wrapped[0], $routes);
    }

    private function findRouteByAction(array $routes, string $action): ?array
    {
        foreach ($routes as $wrapped) {
            if ($wrapped[0]['action'] === $action) {
                return $wrapped[0];
            }
        }

        return null;
    }

    public function testAnnotationCollectorWithAttributesGetterMode(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [AnnotatedController::class],
            getter: true,
            attributes: true
        );

        $this->assertIsArray($routes);
        $this->assertCount(3, $routes);

        $flat = $this->flattenRoutes($routes);
        $actions = array_column($flat, 'action');
        $this->assertContains('actionIndex', $actions);
        $this->assertContains('actionEdit', $actions);
        $this->assertContains('actionView', $actions);
    }

    public function testAnnotationCollectorSetsControllerAndMethod(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [AnnotatedController::class],
            getter: true,
            attributes: true
        );

        $route = $this->findRouteByAction($routes, 'actionIndex');

        $this->assertNotNull($route);
        $this->assertEquals(AnnotatedController::class, $route['controller']);
        $this->assertEquals('actionIndex', $route['action']);
        $this->assertEquals('GET', $route['method']);
        $this->assertEquals('annotated/index', $route['url']);
    }

    public function testAnnotationCollectorWithRegistrationMode(): void
    {
        $router = $this->getRouter();
        $result = $router->annotationCollector(
            [AnnotatedController::class],
            getter: false,
            attributes: true
        );

        $this->assertNull($result);
    }

    public function testAnnotationCollectorWithInvalidController(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Remove the InvalidController controller from the routes.php file.");

        $router = $this->getRouter();
        $router->annotationCollector(['InvalidController']);
    }

    public function testAnnotationCollectorWithMiddleware(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [AnnotatedController::class],
            getter: true,
            attributes: true
        );

        $route = $this->findRouteByAction($routes, 'actionIndex');

        $this->assertNotNull($route);
        $this->assertArrayHasKey('middleware', $route);
        $this->assertArrayHasKey('before', $route['middleware']);
        $this->assertEquals([['AuthMiddleware', ['admin']]], $route['middleware']['before']);
    }

    public function testAnnotationCollectorWithAfterMiddleware(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [AnnotatedController::class],
            getter: true,
            attributes: true
        );

        $route = $this->findRouteByAction($routes, 'actionEdit');

        $this->assertNotNull($route);
        $this->assertArrayHasKey('before', $route['middleware']);
        $this->assertArrayHasKey('after', $route['middleware']);
        $this->assertEquals([['LogMiddleware', ['edit']]], $route['middleware']['after']);
    }

    public function testAnnotationCollectorWithoutMiddleware(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [AnnotatedController::class],
            getter: true,
            attributes: true
        );

        $route = $this->findRouteByAction($routes, 'actionView');

        $this->assertNotNull($route);
        $this->assertArrayHasKey('middleware', $route);
        $this->assertEmpty($route['middleware']);
    }

    public function testAnnotationCollectorWithEmptyController(): void
    {
        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [MainController::class],
            getter: true,
            attributes: true
        );

        $this->assertIsArray($routes);
        $this->assertEmpty($routes);
    }

    public function testHandleAnnotationMiddlewareWithParams(): void
    {
        $router = $this->getRouter();
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('handleAnnotationMiddleware');
        $method->setAccessible(true);

        $result = $method->invoke($router, [
            ['name' => 'AuthMiddleware', 'params' => ['admin']],
        ]);

        $this->assertEquals([['AuthMiddleware', ['admin']]], $result);
    }

    public function testHandleAnnotationMiddlewareWithoutParams(): void
    {
        $router = $this->getRouter();
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('handleAnnotationMiddleware');
        $method->setAccessible(true);

        $result = $method->invoke($router, [
            ['name' => 'AuthMiddleware'],
        ]);

        $this->assertEquals(['AuthMiddleware'], $result);
    }

    public function testHandleAnnotationMiddlewareMultiple(): void
    {
        $router = $this->getRouter();
        $reflection = new \ReflectionClass($router);
        $method = $reflection->getMethod('handleAnnotationMiddleware');
        $method->setAccessible(true);

        $result = $method->invoke($router, [
            ['name' => 'AuthMiddleware', 'params' => ['admin']],
            ['name' => 'LogMiddleware'],
            ['name' => 'CacheMiddleware', 'params' => ['3600']],
        ]);

        $this->assertEquals([
            ['AuthMiddleware', ['admin']],
            'LogMiddleware',
            ['CacheMiddleware', ['3600']],
        ], $result);
    }

    /**
     * Tests that optional segments in square brackets are expanded into multiple routes.
     * Example: 'admin[/item[/page[/:page]]]' should expand to 4 separate routes.
     */
    public function testExpandOptionalSegments(): void
    {
        $controller = new class {
            #[Routing(url: 'admin[/item[/page[/:page]]]')]
            public function adminPanel() {}
        };

        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [get_class($controller)],
            getter: true,
            attributes: true
        );

        $this->assertCount(4, $routes);

        $urls = array_column($this->flattenRoutes($routes), 'url');
        sort($urls);

        $this->assertEquals([
            'admin',
            'admin/item',
            'admin/item/page',
            'admin/item/page/:page',
        ], $urls);
    }

    /**
     * Tests that passing an array of URLs registers multiple routes for the same action.
     */
    public function testArrayOfUrls(): void
    {
        $controller = new class {
            #[Routing(url: ['admin', 'admin/item', 'admin/item/page'])]
            public function adminPanel() {}
        };

        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [get_class($controller)],
            getter: true,
            attributes: true
        );

        $this->assertCount(3, $routes);

        $urls = array_column($this->flattenRoutes($routes), 'url');
        sort($urls);

        $this->assertEquals([
            'admin',
            'admin/item',
            'admin/item/page',
        ], $urls);
    }

    /**
     * Tests that regex parameters like :[a-z]{1,3} are NOT treated as optional segments.
     * The brackets inside regex patterns must be ignored by expandOptionalSegments.
     */
    public function testRegexParametersIgnoredInExpand(): void
    {
        $controller = new class {
            #[Routing(url: 'lang/:[a-z]{1,3}')]
            public function setLanguage() {}

            #[Routing(url: 'page/:[\d]{1,3}')]
            public function showPage() {}
        };

        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [get_class($controller)],
            getter: true,
            attributes: true
        );

        // Each method should produce exactly 1 route (regex brackets are not expanded)
        $this->assertCount(2, $routes);

        $flat = $this->flattenRoutes($routes);
        $urls = array_column($flat, 'url');

        $this->assertContains('lang/:[a-z]{1,3}', $urls);
        $this->assertContains('page/:[\d]{1,3}', $urls);
    }

    /**
     * Tests combined usage of optional segments and regex parameters in one URL.
     * Example: 'admin[/item]/:[a-z]+' should expand to 2 routes, both keeping regex intact.
     */
    public function testExpandWithRegexAndOptionalSegments(): void
    {
        $controller = new class {
            #[Routing(url: 'admin[/item]/:[a-z]+')]
            public function mixedRoute() {}
        };

        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [get_class($controller)],
            getter: true,
            attributes: true
        );

        $this->assertCount(2, $routes);

        $urls = array_column($this->flattenRoutes($routes), 'url');
        sort($urls);

        $this->assertEquals([
            'admin/:[a-z]+',
            'admin/item/:[a-z]+',
        ], $urls);
    }

    /**
     * Tests that expanded routes preserve HTTP method and other route parameters.
     */
    public function testExpandedRoutesPreserveMethod(): void
    {
        $controller = new class {
            #[Routing(url: 'admin[/item[/page]]', method: 'POST')]
            public function adminAction() {}
        };

        $router = $this->getRouter();
        $routes = $router->annotationCollector(
            [get_class($controller)],
            getter: true,
            attributes: true
        );

        $flat = $this->flattenRoutes($routes);

        $this->assertCount(3, $flat);
        foreach ($flat as $route) {
            $this->assertEquals('POST', $route['method']);
            $this->assertEquals('adminAction', $route['action']);
        }
    }
}
