<?php
/**
 * Date: 25.04.17
 * Time: 18:23
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


trait SetRouterTrait
{

    /**
     * @var Router
     */
    protected $router;

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }
}