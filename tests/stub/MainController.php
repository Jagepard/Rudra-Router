<?php

/**
 * Date: 10.04.17
 * Time: 14:30
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace stub\Controllers;


use Rudra\Container\ContainerInterface;
use Rudra\Router\RouterMiddlewareTrait;


/**
 * Class MainController
 *
 * @package stub
 */
class MainController
{

    use RouterMiddlewareTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Routing(url = 'test/123', method = 'GET')
     *
     * @Middleware(name = 'Middleware', params = {int : '1'})
     * @Middleware(name = 'Middleware', params = {int : '3'| qwe : '321'})
     * @Middleware(name = 'Middleware', params = {int : '5'| asd : '456'})
     * @Middleware(name = 'Middleware', params = {int : '7'})
     * @Middleware(name = 'Middleware', params = {int : '9'| qwe : '321'})
     * @Middleware(name = 'Middleware', params = {int : '11'| asd : '456'})
     *
     * @AfterMiddleware(name = 'Middleware', params = {int : '13'| qwe : '321'})
     * @AfterMiddleware(name = 'Middleware', params = {int : '15'| asd : '456'})
     */
    public function actionIndex()
    {
        $this->container()->set('actionIndex', 'actionIndex', 'raw');
    }

    public function actionGet()
    {
        $this->container()->set('actionGet', 'GET', 'raw');
    }

    public function actionPost()
    {
        $this->container()->set('actionPost', 'POST', 'raw');
    }

    public function actionPut()
    {
        $this->container()->set('actionPut', 'PUT', 'raw');
    }

    public function actionPatch()
    {
        $this->container()->set('actionPatch', 'PATCH', 'raw');
    }

    public function actionDelete()
    {
        $this->container()->set('actionDelete', 'DELETE', 'raw');
    }

    public function actionAny()
    {
        $this->container()->set('actionAny', 'ANY', 'raw');
    }

    public function read($params = null)
    {
        $this->container()->set('read', 'read', 'raw');
    }

    public function create()
    {
        $this->container()->set('create', 'create', 'raw');
    }

    public function update($params)
    {
        $this->container()->set('update', 'update', 'raw');
    }

    public function delete($params)
    {
        $this->container()->set('delete', 'delete', 'raw');
    }

    public function init($container, ...$params)
    {
        $this->container = $container;
    }

    public function before(){}
    public function after(){}

    /**
     * @return mixed
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}