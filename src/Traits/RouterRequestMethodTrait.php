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
     */
    public function get(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET', $middleware);
    }

    /**
     * Registers a route with the POST HTTP method.
     */
    public function post(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'POST', $middleware);
    }

    /**
     * Registers a route with the PUT HTTP method.
     */
    public function put(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PUT', $middleware);
    }

    /**
     * Registers a route with the PATCH HTTP method.
     */
    public function patch(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'PATCH', $middleware);
    }

    /**
     * Registers a route with the DELETE HTTP method.
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
     */
    public function any(string $pattern, array|callable $target, array $middleware = []): void
    {
        $this->setRoute($pattern, $target, 'GET|POST|PUT|PATCH|DELETE', $middleware);
    }

    /**
     * Registers RESTful resource routes with explicit plural and singular URL patterns.
     *
     * No magic pluralization — you define exactly what the URLs look like.
     *
     * Creates the following routes:
     *  - GET    {plural}          => actions[0] (index — list all)
     *  - GET    {singular}/:id    => actions[1] (read — get single)
     *  - POST   {plural}          => actions[2] (create)
     *  - PUT    {singular}/:id    => actions[3] (full update)
     *  - PATCH  {singular}/:id    => actions[3] (partial update)
     *  - DELETE {singular}/:id    => actions[4] (delete)
     */
    public function resource(string $plural, string $singular, string $controller,
        array $actions = ['index', 'read', 'create', 'update', 'delete']
    ): void {
        [$index, $read, $create, $update, $delete] = $actions;

        $plural   = ltrim($plural, '/');
        $singular = ltrim($singular, '/');

        // GET    api/users       => index
        $this->set([
            'method'     => 'GET',
            'url'        => $plural,
            'controller' => $controller,
            'action'     => $index,
        ]);

        // GET    api/user/:id    => read
        $this->set([
            'method'     => 'GET',
            'url'        => $singular . '/:id',
            'controller' => $controller,
            'action'     => $read,
        ]);

        // POST   api/users       => create
        $this->set([
            'method'     => 'POST',
            'url'        => $plural,
            'controller' => $controller,
            'action'     => $create,
        ]);

        // PUT    api/user/:id    => update
        $this->set([
            'method'     => 'PUT',
            'url'        => $singular . '/:id',
            'controller' => $controller,
            'action'     => $update,
        ]);

        // PATCH  api/user/:id    => update
        $this->set([
            'method'     => 'PATCH',
            'url'        => $singular . '/:id',
            'controller' => $controller,
            'action'     => $update,
        ]);

        // DELETE api/user/:id    => delete
        $this->set([
            'method'     => 'DELETE',
            'url'        => $singular . '/:id',
            'controller' => $controller,
            'action'     => $delete,
        ]);
    }

    /**
     * The method constructs a route definition and passes it to the `set()` method for registration.
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
