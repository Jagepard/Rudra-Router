<?php

declare(strict_types = 1);

/**
 * Date: 05.09.16
 * Time: 14:51
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class Router
 *
 * @package Rudra
 */
class Router implements RouterInterface
{

    use RouterMethodTrait;
    use RouterMatchTrait;

    /**
     * @var bool
     */
    protected $token = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $templateEngine;

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $templateEngine
     */
    public function __construct(ContainerInterface $container, string $namespace, string $templateEngine)
    {
        $this->container      = $container;
        $this->namespace      = $namespace;
        $this->templateEngine = $templateEngine;
        set_exception_handler([new RouterException(), 'handler']);
    }

    /**
     * @param array $route
     *
     * @return bool
     */
    public function set(array $route)
    {
        if ($this->container()->hasPost('_method')) {
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

        if (($this->container()->getServer('REQUEST_METHOD') === 'GET')
            || $this->container()->getServer('REQUEST_METHOD') === 'POST'
        ) {
            $this->matchHttpMethod($route);
            return false;
        }

        if (($this->container()->getServer('REQUEST_METHOD') === 'PUT')
            || ($this->container()->getServer('REQUEST_METHOD') === 'PATCH')
            || ($this->container()->getServer('REQUEST_METHOD') === 'DELETE')
        ) {
            $settersName = 'set' . ucfirst(strtolower($this->container()->getServer('REQUEST_METHOD')));
            parse_str(file_get_contents('php://input'), $data);
            $this->container()->$settersName($data);
            $this->matchHttpMethod($route);
            return false;
        }
    }

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     *
     * @throws RouterException
     */
    public function annotation(string $class, string $method, int $number = 0): void
    {
        $result = $this->container()->get('annotation')->getMethodAnnotations($class, $method);

        if (isset($result['Routing'])) {
            $this->set($result['Routing'][$number]['url'], [$class, $method]);
        }
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    /**
     * @return ContainerInterface
     */
    protected function container(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    protected function namespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    protected function templateEngine()
    {
        return $this->templateEngine;
    }
}
