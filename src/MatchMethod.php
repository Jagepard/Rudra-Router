<?php
/**
 * Date: 25.04.17
 * Time: 11:15
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


class MatchMethod
{

    use SetContainerTrait;

    protected $matchRequest;

    public function __construct(ContainerInterface $container, MatchRequest $matchRequest)
    {
        $this->container    = $container;
        $this->matchRequest = $matchRequest;
    }

    /**
     * @param $route
     *
     * @return MatchRequest
     */
    public function matchRequest($route)
    {
        return $this->matchRequest->matchRequest($route);;
    }

    /**
     * @param array $route
     */
    public function matchHttpMethod(array $route)
    {
        if (strpos($route['http_method'], '|') !== false) {
            $httpArray = explode('|', $route['http_method']);

            foreach ($httpArray as $httpItem) {
                $route['http_method'] = $httpItem;
                $this->matchRequest($route);
            }
        } else {
            $this->matchRequest($route);
        }
    }
}