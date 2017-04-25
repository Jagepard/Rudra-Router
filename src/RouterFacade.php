<?php

declare(strict_types = 1);

/**
 * Date: 12.04.17
 * Time: 10:01
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class RouterFacade
 *
 * @package Rudra
 */
class RouterFacade
{

    use SetContainerTrait;

    protected $requestMethod;

    protected $matchMethod;

    public function __construct(ContainerInterface $container, RequestMethod $requestMethod, MatchMethod $matchMethod)
    {
        $this->container     = $container;
        $this->requestMethod = $requestMethod;
        $this->matchMethod   = $matchMethod;
    }

    /**
     * @return RequestMethod
     */
    public function requestMethod(): RequestMethod
    {
        return $this->requestMethod;
    }

    /**
     * @return MatchMethod
     */
    public function matchMethod(): MatchMethod
    {
        return $this->matchMethod;
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route)
    {
        if ($this->container()->hasPost('_method') && $this->container()->getServer('REQUEST_METHOD') === 'POST') {
            $this->requestMethod()->setRequestMethod();
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'GET')
            || $this->container()->getServer('REQUEST_METHOD') === 'POST'
        ) {
            $this->matchHttpMethod($route);
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'PUT')
            || ($this->container()->getServer('REQUEST_METHOD') === 'PATCH')
            || ($this->container()->getServer('REQUEST_METHOD') === 'DELETE')
        ) {
            $settersName = 'set' . ucfirst(strtolower($this->container()->getServer('REQUEST_METHOD')));
            parse_str(file_get_contents('php://input'), $data);
            $this->container()->$settersName($data);
            $this->matchHttpMethod($route);
        }
    } // @codeCoverageIgnore
}
