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
     * @param null $middleware
     *
     * @return bool
     */
    public function __invoke($middleware = null)
    {
        if (!is_array($middleware[0])) {
            $middleware[0] = $middleware;
            unset($middleware[1]);
        }

        if (isset($middleware[0][1])) {
            if ($middleware[0][1]['int'] % 2) {
                echo json_encode($_SERVER);
            }
        }

        $this->container()->set('middleware', 'middleware', 'raw');

        if (isset($middleware[1])) {
            (new $middleware[1][0]($this->container()))(array_pop($middleware));
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