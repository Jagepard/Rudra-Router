<?php declare(strict_types=1);

namespace Rudra\Router\Tests\Stub\Controllers;

use Rudra\Annotations\AfterMiddleware;
use Rudra\Annotations\Middleware;
use Rudra\Annotations\Routing;
use Rudra\Container\Facades\Rudra;

class AnnotatedController
{
    #[Routing(url: "annotated/index", method: "GET")]
    #[Middleware(name: "AuthMiddleware", params: ["admin"])]
    public function actionIndex(): void
    {
        Rudra::config()->set(["annotatedIndex" => "annotatedIndex"]);
    }

    #[Routing(url: "annotated/edit/:id", method: "POST")]
    #[Middleware(name: "AuthMiddleware")]
    #[AfterMiddleware(name: "LogMiddleware", params: ["edit"])]
    public function actionEdit(string $id): void
    {
        Rudra::config()->set(["annotatedEdit" => "annotatedEdit"]);
    }

    #[Routing(url: "annotated/view/:id", method: "GET")]
    public function actionView(string $id): void
    {
        Rudra::config()->set(["annotatedView" => "annotatedView"]);
    }

    public function shipInit() {}
    public function containerInit() {}
    public function init() {}
    public function before() {}
    public function after() {}
}
