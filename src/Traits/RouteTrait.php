<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Container\Facades\Rudra;
use Rudra\Exceptions\RouterException;
use Rudra\Router\RouterFacade;

trait RouteTrait
{
    protected function route(string $bundle, string $driver)
    {
        RouterFacade::setNamespace(Rudra::config()->get("namespaces")[$bundle]);
        RouterFacade::annotationCollector($this->getRoutes($bundle, $driver));
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
     */
    protected function getRoutes(string $bundle, string $driver): array
    {
        $path = "../app/" . $bundle . "/Routes/". $driver;

        if (file_exists($path . ".php")) {
            return require_once $path . ".php";
        }
    }

    protected function handleException()
    {
        throw new RouterException("404");
    } // @codeCoverageIgnore
}
