[![Build Status](https://app.travis-ci.com/Jagepard/Rudra-Router.svg?branch=master)](https://app.travis-ci.com/Jagepard/Rudra-Router)
[![codecov](https://codecov.io/gh/Jagepard/Rudra-Router/branch/master/graph/badge.svg)](https://codecov.io/gh/Jagepard/Rudra-Router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Router/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Router)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-router/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-router)
-----
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/rudra/router/v/stable)](https://packagist.org/packages/rudra/router)
[![Total Downloads](https://poser.pugx.org/rudra/router/downloads)](https://packagist.org/packages/rudra/router)
![GitHub](https://img.shields.io/github/license/jagepard/Rudra-Router.svg)

# Rudra-Router

#### Устанавливаем маршрут callback/{id} для http метода GET
_выполняет лямбда-функцию_
```php
$router->get('callback/{id}', function () {
    echo 'Hello world!';
});
```
_Для вызова через Фасад Rudra-Container_
```php
use Rudra\Router\RouterFacade as Router;

Router::get('callback/{id}', function () {
    echo 'Hello world!';
});
```
_вызывает MainController::read_
```php
$router->get('read/{id}', [MainController::class, 'read']);
```
_вызывает MainController::read при помощи добавления аннотаций к MainController_
```php
/**
 * @Routing(url = ''read/{id}')
 */
public function read()
```
_вызывает MainController::read_ и добавляет middleware с ключами before или after соответственно_
```php
$router->get('read/page',  [MainController::class, 'read'], ['before'  => [Middleware::class]);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'read/page')
 * @Middleware(name = 'App\Middleware\Middleware')
 */
public function read()
```
_Для сбора аннотаций необходимо передать массив в annotationCollector_
```php
$router->annotationCollector([
    \App\Controllers\MainController::class,
    \App\Controllers\SecondController::class,
]);
```
```php
Router::annotationCollector([
    \App\Controllers\MainController::class,
    \App\Controllers\SecondController::class,
]);
```
_С параметрами для middleware_
```php
$router->get('', [MainController::class, 'read'], [
    'before' => [FirstMidddleware::class, [SecondMidddleware::class, ['int' => 456, new \stdClass]]],
    'after'  => [FirstMidddleware::class, [SecondMidddleware::class, ['int' => 456, new \stdClass]]]
]);
```
_в аннотациях_
```php
/**
 * @Routing(url = '')
 * @Middleware(name = 'App\Middleware\FirstMidddleware')
 * @Middleware(name = 'App\Middleware\SecondMidddleware', params = {int : '456'})
 * @AfterMiddleware(name = 'App\Middleware\FirstMidddleware')
 * @AfterMiddleware(name = 'App\Middleware\SecondMidddleware', params = {int : '456'})
 */
public function read()
```
_При передаче параметров в middleware необходимо добавлять параметр "array $params"_
```php
public function __invoke(array $params, array $middlewares)
```
_Если параметры не передаются, то:_
```php
public function __invoke(array $middlewares)
```
_Следующие вызовы без параметров равны_
```php
'before' => [FirstMidddleware::class, SecondMidddleware::class]],
'before' => [[FirstMidddleware::class], [SecondMidddleware::class]]
```
#### Устанавливаем маршрут create/{id} для http метода POST
_вызывает MainController::create_
```php
$router->post('create/{id}', [MainController::class, 'create']);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'create/{id}', method = 'POST')
 */
public function create()
```
#### Устанавливаем маршрут update/{id} для http метода PUT
_вызывает MainController::update_
```php
$router->put('update/{id}', [MainController::class, 'update']);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'update/{id}', method = 'PUT')
 */
public function update()
```
#### Устанавливаем маршрут update/{id} для http метода PATCH
_вызывает MainController::update_
```php
$router->patch('update/{id}', [MainController::class, 'update']);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'update/{id}', method = 'PATCH')
 */
public function update()
```
#### Устанавливаем маршрут delete/{id} для http метода DELETE
_вызывает MainController::delete_
```php
$router->delete('delete/{id}', [MainController::class, 'delete']);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'delete/{id}', method = 'DELETE')
 */
public function delete()
```
#### Устанавливаем маршрут any/{id} для http методов GET|POST|PUT|PATCH|DELETE
_вызывает MainController::any_
```php
$router->any('any/{id}', [MainController::class, 'any']);
```
_в аннотациях_
```php
/**
 * @Routing(url = 'any/{id}', method = 'GET|POST|PUT|PATCH|DELETE')
 */
public function any()
```
#### Устанавливаем ресурс для маршрута api/{id}, методы GET|POST|PUT|DELETE
_вызывает MainController::read для GET_

_вызывает MainController::create для POST_

_вызывает MainController::update для PUT_

_вызывает MainController::delete для DELETE_
```php
$router->resource('api/{id}', MainController::class);
```
Изменить методы контроллера по умолчанию можно передав массив с вашими именами
```php
$router->resource('api/{id}', MainController::class, ['actionIndex', 'actionAdd', 'actionUpdate', 'actionDrop']);
```
##### Вариант объявления маршрута методом set
#### Устанавливаем маршрут /test/{id} для http методов DELETE|PUT
_выполняет лямбда-функцию_
```php
$router->set(['/test/page', 'POST|PUT', function () {
            echo 'Hello world!';
        }
]);
```
_вызывает MainController::actionIndex_
```php
$router->set(['/test/{id}', 'DELETE|PUT', [MainController::class, 'actionIndex'], [
        'before' => [First::class, Second::class],
        'after'  => [[First::class], [Second::class]]
]]);
```
_Пример Middleware_
```php
<?php

namespace App\Middleware;

use Rudra\Router\Router;
use Rudra\Router\MiddlewareInterface;

class FirstMiddleware extends Router implements MiddlewareInterface
{
    public function __invoke(array $middlewares)
    {
        $this->next($middlewares);
    }

    public function next(array $middlewares): void
    {
        $this->handleMiddleware($middlewares);
    }
}
```
_Пример Middleware с параметрами с использованием Фасада_
```php
<?php

namespace App\Middleware;

use Rudra\Router\MiddlewareInterface;
use Rudra\Router\RouterFacade as Router;

class SecondMiddleware implements MiddlewareInterface
{
    public function __invoke(array $params, array $middlewares)
    {
        var_dump($params);
        $this->next($middlewares);
    }
    
    public function next(array $middlewares): void
    {
        Router::handleMiddleware($middlewares);
    }
}
```
