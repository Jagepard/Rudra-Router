<?php 

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router;

use Rudra\Exceptions\RouterException;

interface RouterInterface
{
    /**
     * Sets the route, parsing HTTP methods (if multiple are specified via |).
     * Registers a route handler for each method.
     * -------------------------
     * Устанавливает маршрут, разбирая HTTP-методы (если указано несколько через |).
     * Для каждого метода регистрирует обработчик маршрута.
     *
     * @param array $route
     * @return void
     */
    public function set(array $route): void;
    
    /**
     * Calls the controller and its method directly, performing the full lifecycle:
     * This method is used to fully dispatch a route after matching it with the current request.
     * -------------------------
     * Вызывает контроллер и его метод напрямую, выполняя полный жизненный цикл:
     * Метод используется для полной диспетчеризации маршрута после его совпадения с текущим запросом.
     *
     * @param array $route
     * @param array|null $params
     * @return void
     * @throws RouterException
     */
    public function directCall(array $route, ?array $params = null): void;
}
