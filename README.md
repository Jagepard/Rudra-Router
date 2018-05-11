[![Build Status](https://travis-ci.org/Jagepard/Rudra-Router.svg?branch=master)](https://travis-ci.org/Jagepard/Rudra-Router)
[![codecov](https://codecov.io/gh/Jagepard/Rudra-Router/branch/master/graph/badge.svg)](https://codecov.io/gh/Jagepard/Rudra-Router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Router/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Router)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/86edd8dbec394319afd00d7c5eff88bc)](https://www.codacy.com/app/Jagepard/Rudra-Router?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Jagepard/Rudra-Router&amp;utm_campaign=Badge_Grade)
-----
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/rudra/validation/v/stable)](https://packagist.org/packages/rudra/router)
[![Total Downloads](https://poser.pugx.org/rudra/validation/downloads)](https://packagist.org/packages/rudra/router)
[![License: GPL-3.0-or-later](https://img.shields.io/badge/license-GPL--3.0--or--later-498e7f.svg)](https://www.gnu.org/licenses/gpl-3.0)

# Rudra-Router

#### Добавление Rudra-Router в контейнер
```php
use Rudra\Container as Rudra;
use Rudra\ContainerInterface;

Rudra::app()->setBinding(ContainerInterface::class, Rudra::$app);
Rudra::$app->set('router', 'Rudra\Router', ['namespace' => 'stub\\', 'templateEngine' => 'twig']);
$router = Rudra::$app->get('router');
```
#### Устанавливаем маршрут /test/{id} для http методов DELETE|PUT
_выполняет лямбда-функцию_
```php
$router->set([
        'pattern' => '/test/page',
        'http_method' => 'POST|PUT',
        'method'  => function () {
            $this->container()->set('closure', 'closure', 'raw');
        }
    ]
);
```
_вызывает stub\\MainController::actionIndex_
```php
$router->set([
        'pattern'     => '/test/{id}',
        'http_method' => 'DELETE|PUT',
        'controller'  => 'stub\\MainController::namespace',
        'method'      => 'actionIndex'
    ]
);
```
#### Устанавливаем ресурс для маршрута api/{id}, методы GET|POST|PUT|DELETE
_вызывает stub\\MainController::read для GET_

_вызывает stub\\MainController::create для POST_

_вызывает stub\\MainController::update для PUT_

_вызывает stub\\MainController::delete для DELETE_
```php
$router->resource([
    'pattern'     => 'api/{id}',
    'controller'  => 'MainController'
]);
```
#### Устанавливаем маршрут 123/122 и добавляем middleware
_вызывает stub\\MainController::read_
```php
$router->middleware('get', [
    'pattern'     => '123/122',
    'controller'  => 'MainController',
    'method'      => 'read',
    'middleware'  => [['stub\\Middleware', ['int' => 123]], ['stub\\Middleware', ['int' => 125]]]
]);
```
#### Устанавливаем маршрут 123/{id} для http метода GET
_вызывает stub\\MainController::read_
```php
$router->get([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'read'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода POST
_вызывает stub\\MainController::create_
```php
$router->post([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'create'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода PUT
_вызывает stub\\MainController::update_
```php
$router->put([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'update'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода PATCH
_вызывает stub\\MainController::update_
```php
$router->patch([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'update'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода DELETE
_вызывает stub\\MainController::delete_
```php
$router->delete([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'delete'
]);
```
