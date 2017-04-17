<?php

/**
 * Date: 10.04.17
 * Time: 14:30
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace stub;


use Rudra\ContainerInterface;
use Rudra\Container;


/**
 * Class MainController
 *
 * @package stub
 */
class MainController
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Routing(url = 'test/123'| method = 'GET')
     */
    public function actionIndex()
    {
        $this->container()->set('actionIndex', 'actionIndex', 'raw');
        !d(__METHOD__);
    }

    public function actionGet()
    {
        $this->container()->set('actionGet', 'GET', 'raw');
        !d(__METHOD__);
    }

    public function actionPost()
    {
        $this->container()->set('actionPost', 'POST', 'raw');
        !d(__METHOD__);
    }

    public function actionPut()
    {
        $this->container()->set('actionPut', 'PUT', 'raw');
        !d(__METHOD__);
    }

    public function actionPatch()
    {
        $this->container()->set('actionPatch', 'PATCH', 'raw');
        !d(__METHOD__);
    }

    public function actionDelete()
    {
        $this->container()->set('actionDelete', 'DELETE', 'raw');
        !d(__METHOD__);
    }

    public function actionAny()
    {
        $this->container()->set('actionAny', 'ANY', 'raw');
        !d(__METHOD__);
    }

    public function read($params = null)
    {
        $this->container()->set('read', 'read', 'raw');
        !d(__METHOD__);
    }

    public function create()
    {
        $this->container()->set('create', 'create', 'raw');
        !d(__METHOD__);
    }

    public function update($params)
    {
        $this->container()->set('update', 'update', 'raw');
        !d(__METHOD__);
    }

    public function delete($params)
    {
        $this->container()->set('delete', 'delete', 'raw');
        !d(__METHOD__);
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