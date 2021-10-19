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

# Rudra-Router | [API](https://github.com/Jagepard/Rudra-Router/blob/master/docs.md "Documentation API")

#### Устанавливаем маршрут 123/{id} для http метода GET
_выполняет лямбда-функцию_
```php
$router->get('123/{id}', function () {
    echo 'Hello world!';
});
```
_вызывает stub\\MainController::read_
```php
$router->get('123/{id}', [MainController::class, 'read']);
```
_вызывает stub\\MainController::read и добавляет middleware с ключами before или after соответственно_
```php
$router->get('123/122',  [MainController::class, 'read'], ['before'  => [Middleware::class]);
```
#### Устанавливаем маршрут 123/{id} для http метода POST
_вызывает stub\\MainController::create_
```php
$router->post('123/{id}', [MainController::class, 'create']);
```
#### Устанавливаем маршрут 123/{id} для http метода PUT
_вызывает stub\\MainController::update_
```php
$router->put('123/{id}', [MainController::class, 'update']);
```
#### Устанавливаем маршрут 123/{id} для http метода PATCH
_вызывает stub\\MainController::update_
```php
$router->patch('123/{id}', [MainController::class, 'update']);
```
#### Устанавливаем маршрут 123/{id} для http метода DELETE
_вызывает stub\\MainController::delete_
```php
$router->delete('123/{id}', [MainController::class, 'delete']);
```
#### Устанавливаем ресурс для маршрута api/{id}, методы GET|POST|PUT|DELETE
_вызывает stub\\MainController::read для GET_

_вызывает stub\\MainController::create для POST_

_вызывает stub\\MainController::update для PUT_

_вызывает stub\\MainController::delete для DELETE_
```php
$router->resource('api/{id}', MainController::class);
```
Изменить методы контроллера по умолчанию можно передав массив с вашими именами
```php
$router->resource('api/{id}', MainController::class, ['actionIndex', 'actionAdd', 'actionUpdate', 'actionDrop']);
```
##### Вариант объявления маршрута массивом ключ => значение
#### Устанавливаем маршрут /test/{id} для http методов DELETE|PUT
_выполняет лямбда-функцию_
```php
$router->set(['/test/page', 'POST|PUT', function () {
            $this->container()->set('closure', 'closure', 'raw');
        }
    ]
);
```
_вызывает stub\\MainController::actionIndex_
```php
$router->set(['/test/{id}', 'DELETE|PUT', [MainController::class, 'actionIndex']]);
```
