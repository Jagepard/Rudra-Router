<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

trait RouterRequestMethodTrait
{
    /**
     * @param array $route
     */
    abstract public function set(array $route): void;

    public function get(array $route): void { $route['method'] = 'GET';  $this->set($route); }
    public function post(array $route): void { $route['method'] = 'POST'; $this->set($route); }
    public function put(array $route): void { $route['method'] = 'PUT';   $this->set($route); }
    public function patch(array $route): void { $route['method'] = 'PATCH'; $this->set($route); }
    public function delete(array $route): void { $route['method'] = 'DELETE'; $this->set($route); }

    public function any(array $route): void {
        $route['method'] = 'GET|POST|PUT|PATCH|DELETE';
        $this->set($route);
    }

    /**
     * @param array $route
     * @param array $actions
     */
    public function resource(array $route, array $actions = ['read', 'create', 'update', 'delete']): void
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

        $this->set($route);
    }
}
