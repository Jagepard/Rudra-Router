<?php
/**
 * Date: 25.04.17
 * Time: 10:48
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class MatchRequestMethod
 *
 * @package Rudra
 */
class MatchRequestMethod
{

    use SetContainerTrait;

    /**
     * @param string|null $param
     *
     * @return mixed
     */
    public function setRequestMethod(string $param = null)
    {
        if ($param === 'REST') {
            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $this->container()->setServer('REQUEST_METHOD', 'PUT');
                    $route['http_method'] = 'PUT';
                    $route['method']      = 'update';

                    return $route;
                case 'PATCH':
                    $this->container()->setServer('REQUEST_METHOD', 'PATCH');
                    $route['http_method'] = 'PATCH';
                    $route['method']      = 'update';

                    return $route;
                case 'DELETE':
                    $this->container()->setServer('REQUEST_METHOD', 'DELETE');
                    $route['http_method'] = 'DELETE';
                    $route['method']      = 'delete';

                    return $route;
            }
        } else {
            switch ($this->container()->getPost('_method')) {
                case 'PUT':
                    $this->container()->setServer('REQUEST_METHOD', 'PUT');
                    break;
                case 'PATCH':
                    $this->container()->setServer('REQUEST_METHOD', 'PATCH');
                    break;
                case 'DELETE':
                    $this->container()->setServer('REQUEST_METHOD', 'DELETE');
                    break;
            }
        }
    }
}
