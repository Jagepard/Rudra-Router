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
     * @param null $middleware
     *
     * @return bool
     */
    public function __invoke($middleware = null)
    {
        $middleware = $this->handleArray($middleware);

        // StartMiddleware

        if ($middleware[0][1]['int'] % 2) {
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
        if (isset($middleware[1])) {
            $middleware[1][0] = $this->container()->get('router')->setClassName($middleware[1][0], 'middlewareNamespace');
            (new $middleware[1][0]($this->container()))(array_pop($middleware));
        }
    }

    /**
     * @param $middleware
     *
     * @return mixed
     */
    protected function handleArray($middleware)
    {
        if (!is_array($middleware[0])) {
            $middleware[0] = $middleware;
            unset($middleware[1]);

            return $middleware;
        }

        return $middleware;
    }
}