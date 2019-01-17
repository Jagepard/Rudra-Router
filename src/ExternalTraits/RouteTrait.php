<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\ExternalTraits;

use Symfony\Component\Yaml\Yaml;
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
     * @param string $driver
     */
    protected function route(string $bundle, string $driver)
    {
        rudra()->get('router')->setNamespace(config('namespaces', $bundle));
        rudra()->get('router')->annotationCollector($this->getParams($bundle, $driver));
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
     * @param string $driver
     * @return array
     */
    protected function getParams(string $bundle, string $driver): array
    {
        return Yaml::parse(file_get_contents('../app/' . $bundle . '/Routes/'. $driver . '.yml'));
//        return require_once '../app/' . $bundle . '/Routes/'. $route . '.php';
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException($this->container, '404');
    } // @codeCoverageIgnore
}
