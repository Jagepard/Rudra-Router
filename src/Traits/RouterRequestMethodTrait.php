<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router\Traits;

trait RouterRequestMethodTrait
{
    /**
     * Registers a route with the GET HTTP method.
     * --------------------
     * Регистрирует маршрут с использованием метода GET.
     *
     * @param string $pattern
     * @param array|callable $target
     * @param array $middleware
     */
    public function get(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET', $middleware);
    }

    /**
     * Registers a route with the POST HTTP method.
     * --------------------
     * Регистрирует маршрут с использованием метода POST.
     *
     * @param array $route
     */
    public function post(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'POST', $middleware);
    }

    /**
     * Registers a route with the PUT HTTP method.
     * --------------------
     * Регистрирует маршрут с использованием метода PUT.
     *
     * @param array $route
     */
    public function put(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PUT', $middleware);
    }

    /**
     * Registers a route with the PATCH HTTP method.
     * --------------------
     * Регистрирует маршрут с использованием метода PATCH.
     *
     * @param array $route
     */
    public function patch(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PATCH', $middleware);
    }

    /**
     * Registers a route with the DELETE HTTP method.
     * --------------------
     * Регистрирует маршрут с использованием метода DELETE.
     *
     * @param array $route
     */
    public function delete(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'DELETE', $middleware);
    }

    /**
     * Registers a route that supports all HTTP methods.
     *
     * Sets the method to a pipe-separated string ('GET|POST|PUT|PATCH|DELETE'),
     * allowing the same route to handle multiple request types.
     * --------------------
     * Регистрирует маршрут, поддерживающий все HTTP-методы.
     *
     * Устанавливает метод как строку с разделителем | ('GET|POST|PUT|PATCH|DELETE'),
     * что позволяет использовать один маршрут для нескольких типов запросов.
     *
     * @param array $route
     */
    public function any(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET|POST|PUT|PATCH|DELETE', $middleware);
    }

    /**
     * Registers a resource route, mapping standard actions to controller methods.
     *
     * Supports common CRUD operations by default:
     * - GET    => read
     * - POST   => create
     * - PUT    => update
     * - DELETE => delete
     *
     * Can be customized with an optional $actions array.
     * --------------------
     * Регистрирует ресурсный маршрут, связывая стандартные действия с методами контроллера.
     *
     * По умолчанию поддерживает CRUD-операции:
     * - GET    => read
     * - POST   => create
     * - PUT    => update
     * - DELETE => delete
     *
     * Может быть переопределён с помощью массива $actions.
     *
     * @param  string $pattern
     * @param  string $controller
     * @param  array  $actions
     * @return void
     */
    public function resource(string $pattern, string $controller, array $actions = ['read', 'create', 'update', 'delete']): void
    {
        $request = $this->rudra->request();
        $server  = $request->server();
        $post    = $request->post();

        $requestMethod = $server->get('REQUEST_METHOD');
        $httpMethod = $requestMethod === 'POST' && $post->has('_method')
            ? strtoupper($post->get('_method'))
            : $requestMethod;

        switch ($httpMethod) {
            case 'GET':
                $route['method'] = 'GET';
                $route['action'] = $actions[0]; // read
                break;
            case 'POST':
                $route['method'] = 'POST';
                $route['action'] = $actions[1]; // create
                break;
            case 'PUT':
            case 'PATCH':
                $route['method'] = $httpMethod;
                $route['action'] = $actions[2]; // update
                break;
            case 'DELETE':
                $route['method'] = 'DELETE';
                $route['action'] = $actions[3]; // delete
                break;
            default:
                return; // Неизвестный метод — игнорируем
        }

        $route['url'] = $pattern;
        $route['controller'] = $controller;

        $this->set($route);
    }

    /**
     * The method constructs a route definition and passes it to the `set()` method for registration.
     * --------------------
     * Метод формирует определение маршрута и передает его в метод `set()` для регистрации.
     *
     * @param string $pattern
     * @param mixed  $target
     * @param string $httpMethod
     * @param array  $middleware
     */
    protected function setRoute(string $pattern, $target, string $httpMethod, array $middleware = []): void
    {
        $route['method'] = $httpMethod;
        $route['url']    = $pattern;

        if (count($middleware)) {
            if (array_key_exists('before', $middleware)) {
                $route['middleware']['before'] = $middleware['before'];
            }

            if (array_key_exists('after', $middleware)) {
                $route['middleware']['after'] = $middleware['after'];
            }
        }

        if (is_callable($target)) {
            $route['controller'] = $target;
        } elseif (is_array($target)) {
            $route['controller'] = $target[0];
            $route['action']     = $target[1];
        }

        $this->set($route);
    }
}
