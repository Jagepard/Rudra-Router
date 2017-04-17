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
    public function __construct(ContainerInterface $container, $route)
    {
        $this->container = $container;
        $this->route     = $route;
    }

    /**
     * @return string
     */
    public function __invoke($params)
    {
        if ($params % 2) {
            header('Location: https://gist.github.com/Jagepard/0743e3e11ccc3e55025aa5424fb9d723');
            return false;
        }

        return $next();
    }

    /**
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }
}