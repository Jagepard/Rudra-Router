<?php

namespace Rudra\Router\Tests\Stub\Controllers;

use Rudra\Container\Facades\Rudra;

class MainController
{
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

    public function actionRegexGet()
    {
        Rudra::config()->set(["regex" => "regex"]);
    }

    public function shipInit() {}
    public function containerInit() {}
    public function init() {}
    public function before() {}
    public function after() {}
}
