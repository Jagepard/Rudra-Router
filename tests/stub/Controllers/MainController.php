<?php

namespace Rudra\Router\Tests\Stub\Controllers;

use Rudra\Container\Facades\Rudra;
use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Tests\Stub\Route;
use Rudra\ExternalTraits\RouteTrait;
use Rudra\Interfaces\ContainerInterface;
use Rudra\ExternalTraits\RouterMiddlewareTrait;

class MainController
{
    use \Rudra\Router\Traits\RouterMiddlewareTrait;

    protected RudraInterface $rudra;

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
        Rudra::config()->set(["actionIndex" => "actionIndex"]);
    }

    public function actionGet()
    {
        Rudra::config()->set(["actionGet" => "GET"]);
    }

    public function actionPost()
    {
        Rudra::config()->set(["actionPost" => "POST"]);
    }

    public function actionPut()
    {
        Rudra::config()->set(["actionPut" => "PUT"]);
    }

    public function actionPatch()
    {
        Rudra::config()->set(["actionPatch" => "PATCH"]);
    }

    public function actionDelete()
    {
        Rudra::config()->set(["actionDelete" => "DELETE"]);
    }

    public function actionAny()
    {
        Rudra::config()->set(["actionAny" => "ANY"]);
    }

    public function read($params = null)
    {
        Rudra::config()->set(["read" => "read"]);
    }

    public function create()
    {
        Rudra::config()->set(["create" => "create"]);
    }

    public function update($params)
    {
        Rudra::config()->set(["update" => "update"]);
    }

    public function delete($params)
    {
        Rudra::config()->set(["delete" => "delete"]);
    }

    public function init() {}
    public function eventRegistration() {}
    public function generalPreCall() {}
    public function before() {}
    public function after() {}

    public function __construct(RudraInterface $rudra)
    {
        $this->rudra = $rudra;
        $this->rudra()->config()->set(["environment" => "test"]);
    }

    public function rudra(): RudraInterface
    {
        return $this->rudra;
    }
}
