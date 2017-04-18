[![Build Status](https://travis-ci.org/Jagepard/Rudra-Router.svg?branch=master)](https://travis-ci.org/Jagepard/Rudra-Router)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Router/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Router?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Jagepard/Rudra-Router/?branch=master)
[![Code Climate](https://codeclimate.com/github/Jagepard/Rudra-Router/badges/gpa.svg)](https://codeclimate.com/github/Jagepard/Rudra-Router)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/86edd8dbec394319afd00d7c5eff88bc)](https://www.codacy.com/app/Jagepard/Rudra-Router?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Jagepard/Rudra-Router&amp;utm_campaign=Badge_Grade)
[![Latest Stable Version](https://poser.pugx.org/rudra/validation/v/stable)](https://packagist.org/packages/rudra/router)
[![Total Downloads](https://poser.pugx.org/rudra/validation/downloads)](https://packagist.org/packages/rudra/router)
[![License](https://poser.pugx.org/rudra/validation/license)](https://packagist.org/packages/rudra/router)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1c8d8365-d981-4f4d-94f7-4ebedb8e59cb/big.png)](https://insight.sensiolabs.com/projects/1c8d8365-d981-4f4d-94f7-4ebedb8e59cb)
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
```php
$router->resource([
    'pattern'     => 'api/{id}',
    'controller'  => 'MainController'
]);
```
#### Устанавливаем маршрут 123/122 и добавляем middleware
```php
$router->middleware('get', [
    'pattern'     => '123/122',
    'controller'  => 'MainController',
    'method'      => 'read',
    'middleware'  => [['stub\\Middleware', ['int' => 123]], ['stub\\Middleware', ['int' => 125]]]
]);
```
#### Устанавливаем маршрут 123/{id} для http метода GET
```php
$router->get([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'read'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода POST
```php
$router->post([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'create'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода PUT
```php
$router->put([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'update'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода PATCH
```php
$router->patch([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'update'
]);
```
#### Устанавливаем маршрут 123/{id} для http метода DELETE
```php
$router->delete([
    'pattern'     => '123/{id}',
    'controller'  => 'MainController',
    'method'      => 'delete'
]);
```
