<?php

namespace Rudra\Router\Tests\Stub\Controllers;

use Rudra\Container\Interfaces\ApplicationInterface;
use Rudra\Tests\Stub\Route;
use Rudra\ExternalTraits\RouteTrait;
use Rudra\Interfaces\ContainerInterface;
use Rudra\ExternalTraits\RouterMiddlewareTrait;

class MainController
{
    use \Rudra\Router\Traits\RouteTrait;
    use \Rudra\Router\Traits\RouterMiddlewareTrait;

    protected ApplicationInterface $application;

    public function run()
    {
        return $this->route(Route::class, 'web');
    }

    public function exceptionRoute()
    {
        $this->handleException();
    } // @codeCoverageIgnore

    /**
     * @Routing(url = 'test/123', method = 'GET')
     *
     * @Middleware(name = 'Middleware', params = {int : '1'})
     * @Middleware(name = 'Middleware', params = {int : '3'; qwe : '321'})
     * @Middleware(name = 'Middleware', params = {int : '5'; asd : '456'})
     * @Middleware(name = 'Middleware', params = {int : '7'})
     * @Middleware(name = 'Middleware', params = {int : '9'; qwe : '321'})
     * @Middleware(name = 'Middleware', params = {int : '11'; asd : '456'})
     *
     * @AfterMiddleware(name = 'Middleware', params = {int : '13'; qwe : '321'})
     * @AfterMiddleware(name = 'Middleware', params = {int : '15'; asd : '456'})
     */
    public function actionIndex()
    {
        $this->application()->objects()->set(["actionIndex", ["actionIndex", "raw"]]);
    }

    public function actionGet()
    {
        $this->application()->objects()->set(["actionGet", ["GET", "raw"]]);
    }

    public function actionPost()
    {
        $this->application()->objects()->set(["actionPost", ["POST", "raw"]]);
    }

    public function actionPut()
    {
        $this->application()->objects()->set(["actionPut", ["PUT", "raw"]]);
    }

    public function actionPatch()
    {
        $this->application()->objects()->set(["actionPatch", ["PATCH", "raw"]]);
    }

    public function actionDelete()
    {
        $this->application()->objects()->set(["actionDelete", ["DELETE", "raw"]]);
    }

    public function actionAny()
    {
        $this->application()->objects()->set(["actionAny", ["ANY", "raw"]]);
    }

    public function read($params = null)
    {
        $this->application()->objects()->set(["read", ["read", "raw"]]);
    }

    public function create()
    {
        $this->application()->objects()->set(["create", ["create", "raw"]]);
    }

    public function update($params)
    {
        $this->application()->objects()->set(["update", ["update", "raw"]]);
    }

    public function delete($params)
    {
        $this->application()->objects()->set(["delete", ["delete", "raw"]]);
    }

    public function init() {}
    public function before() {}
    public function after() {}

    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
        $this->application()->config()->set(["environment" => "test"]);
    }

    public function application(): ApplicationInterface
    {
        return $this->application;
    }
}
