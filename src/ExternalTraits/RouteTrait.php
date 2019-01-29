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
        rudra()->get('router')->annotationCollector($this->getRoutes($bundle, $driver));
    }

    /**
     * Собирает маршруты из конфигурации
     *
     * @param array  $namespaces
     * @param string $driver
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
    protected function getRoutes(string $bundle, string $driver): array
    {
        $path = '../app/' . $bundle . '/Routes/'. $driver;

        if (file_exists($path . '.yml')) {
            return Yaml::parse(file_get_contents($path . '.yml'));
        }

        if (file_exists($path . '.php')) {
            return require_once $path . '.php';
        }
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException(rudra(), '404');
    } // @codeCoverageIgnore
}
