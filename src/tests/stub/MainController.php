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
    public function read(...$params)
    {
        var_dump(__METHOD__);
        var_dump($params);
        var_dump($this->container()->getGet());
        Container::$app->set('actionIndex', 123, 'raw');
    }

    public function create()
    {
        var_dump(__METHOD__);
        var_dump($this->container()->getPost());
    }

    public function update(...$params)
    {
        var_dump(__METHOD__);
        var_dump($params);
        var_dump($this->container()->getPut());
    }

    public function delete(...$params)
    {
        var_dump(__METHOD__);
        var_dump($params);
        var_dump($this->container()->getDelete());
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