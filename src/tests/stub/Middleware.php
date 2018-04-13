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


use Rudra\SetContainerTrait;


/**
 * Class Middleware
 *
 * @package stub
 */
class Middleware
{

    use SetContainerTrait;

    /**
     * @param $current
     * @param $middleware
     */
    public function __invoke($current, $middleware)
    {
        // StartMiddleware

       $this->container()->set('middleware', 'middleware', 'raw');

        // EndMiddleware

        $this->next($middleware);
    }

    /**
     * @param $middleware
     */
    protected function next($middleware)
    {
        $this->container()->get('router')->handleMiddleware($middleware);
    }
}