<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\ExternalTraits;

use Rudra\Exceptions\RouterException;
use Rudra\Interfaces\ContainerInterface;

/**
 * Trait RouteTrait
 * @package Rudra\ExternalTraits
 */
trait RouteTrait
{

    /**
     * @param string $bundle
     * @param string $route
     * @return mixed
     */
    protected function route(string $bundle, string $route)
    {
        return $this->container()->new('App\\' . (ucfirst($bundle) . '\\Route'))->run(
            $this->container()->get('router'),
            $this->container()->config('namespaces', $bundle),
            $this->getParams($bundle, $route)
        );
    }

    /**
     * Собирает маршруты из конфигурации
     */
    protected function collect(array $namespaces, string $driver)
    {
        foreach ($namespaces as $bundle => $item) {
            $this->route($bundle, $driver);
        }
    }

    /**
     * Получает массив маршрутов
     *
     * @param string $bundle
     * @param string $route
     * @return array
     */
    protected function getParams(string $bundle, string $route = null): array
    {
        return require_once '../app/' . $bundle . '/Routes/'. $route . '.php';
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException($this->container(), '404');
    } // @codeCoverageIgnore

    /**
     * @param array $keys
     * @return mixed
     */
    protected function withOut(array $keys)
    {
        $namespaces = $this->container()->config('namespaces');

        foreach ($keys as $key) {
            unset($namespaces[$key]);
        }

        return $namespaces;
    }

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
