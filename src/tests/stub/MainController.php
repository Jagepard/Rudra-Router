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
     * @Routing(url = '')
     * @return string
     */
    public function read($params = null)
    {
        d($this->container()->getServer('REQUEST_METHOD'));
        d(__METHOD__);
        d($params);
        Container::$app->set('actionIndex', 123, 'raw');
    }

    public function create()
    {
        d($this->container()->getServer('REQUEST_METHOD'));
        d(__METHOD__);
    }

    public function update($params)
    {
        d($this->container()->getServer('REQUEST_METHOD'));
        d(__METHOD__);
        d($params);
    }

    public function delete($params)
    {
        d($this->container()->getServer('REQUEST_METHOD'));
        d(__METHOD__);
        d($params);
    }

    public function index($params = null)
    {

        d($this->container()->getServer('REQUEST_METHOD'));
        d(__METHOD__);
        d($params);
        Container::$app->set('actionIndex', 123, 'raw');
    }

    public function actionIndex()
    {
        echo json_encode($this->container()->getServer());
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