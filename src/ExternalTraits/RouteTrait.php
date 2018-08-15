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
     * @return mixed
     */
    protected function route(string $bundle)
    {
        return $this->container()->new('App\\' . (ucfirst($bundle) . '\\Route'))->run(
            $this->container()->get('router'),
            $this->container()->config('namespaces', $bundle),
            $this->getParamsPathName($bundle)
        );
    }

    /**
     * Собирает маршруты из конфигурации
     */
    protected function collect()
    {
        foreach ($this->container()->config('namespaces') as $bundle => $item) {
            $this->route($bundle);
        }
    }

    /**
     * Получает массив маршрутов
     *
     * @param string $path
     * @return array
     */
    protected function getParams(string $path): array
    {
        return require_once '../app/' . $path . '/Routes/'. $this->container()->config('database', 'active') . '.php';
    }

    /**
     * @throws RouterException
     */
    protected function handleException()
    {
        throw new RouterException($this->container(), '404');
    } // @codeCoverageIgnore

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
