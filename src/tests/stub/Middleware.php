<?php
/**
 * Date: 17.04.17
 * Time: 14:19
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace stub\Middleware;


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
     * @param $current
     * @param $middleware
     */
    public function __invoke($current, $middleware)
    {
        // StartMiddleware

        if ($current[1]['int'] % 2) {
            echo json_encode($_SERVER);
        }

        $this->container()->set('middleware', 'middleware', 'raw');

        // EndMiddleware

        $this->next($middleware);
    }

    /**
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param $middleware
     */
    protected function next($middleware)
    {
        $this->container()->get('router')->handleMiddleware($middleware);
    }
}