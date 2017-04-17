<?php
/**
 * Date: 17.04.17
 * Time: 14:19
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace stub;


use Rudra\ContainerInterface;


/**
 * Class Middleware
 *
 * @package stub
 */
class Middleware
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Middleware constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param       $middleware
     * @param array ...$params
     *
     * @return bool
     */
    public function __invoke($middleware, ...$params)
    {
        if ($params % 2) {
            header('Location: https://gist.github.com/Jagepard/0743e3e11ccc3e55025aa5424fb9d723');
            return false;
        }

        if (count($middleware)) {
            (new $middleware[0]($this->container(), array_pop($middleware)))(2);
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}