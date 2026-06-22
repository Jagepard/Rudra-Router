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
    public function set(array $route): void;
    public function directCall(array $route, ?array $params = null): void;
}
