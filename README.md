[![PHPunit](https://github.com/Jagepard/Rudra-Router/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Router/actions/workflows/php.yml)
[![Maintainability](https://qlty.sh/badges/d9252114-5cc4-405e-bbf7-6419ec50266f/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Router)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-router/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-router)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Router/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Router?branch=master)
-----

# Rudra-Router

#### Basic installation / Базовая установка
```php
use Rudra\Router\Router;
use Rudra\Container\Rudra;

$router = new Router(Rudra::run());
```
#### Installation for facade use / Установка для использования фасада
```php
use Rudra\Container\Facades\Rudra;  
use Rudra\Router\RouterFacade as Router;
use Rudra\Container\Interfaces\RudraInterface;

Rudra::binding()->set([RudraInterface::class => Rudra::run()]);
```

#### Setting the route / Устанавливаем маршрут callback/:name
```php
$router->get('callback/:name', function ($name) {
    echo "Hello $name!";
});
```
_with Regex_
```php
$router->get('callback/:[\d]{1,3}', function ($name) {
    echo "Hello $name!";
});
```
_To call through the Facade / Для вызова через Фасад_
```php
Router::get('callback/:name', function ($name) {
    echo "Hello $name!";
});
```
_with Regex_
```php
Router::get('callback/:[\d]{1,3}', function ($name) {
    echo "Hello $name!";
});
```
_call / вызывает MainController::read_
```php
$router->get('read/:id', [MainController::class, 'read']);
```
_To call through the Facade / Для вызова через Фасад_
```php
Router::get('read/:id', [MainController::class, 'read']);
```
_call MainController::read with middleware_
```php
$router->get('read/page',  [MainController::class, 'read'], ['before' => [Middleware::class]);
```
_To call through the Facade / Для вызова через Фасад_
```php
Router::get('read/page',  [MainController::class, 'read'], ['before' => [Middleware::class]);
```
_С параметрами для middleware_
```php
$router->get('', [MainController::class, 'read'], [
    'before' => [FirstMidddleware::class, [SecondMidddleware::class, ['int' => 456, new \stdClass]]],
    'after'  => [FirstMidddleware::class, [SecondMidddleware::class, ['int' => 456, new \stdClass]]]
]);
```
_call / вызывает MainController::create_
```php
$router->post('create/:id', [MainController::class, 'create']);
```
_call / вызывает MainController::update_
```php
$router->put('update/:id', [MainController::class, 'update']);
```
_call / вызывает MainController::update_
```php
$router->patch('update/:id', [MainController::class, 'update']);
```
_call / вызывает MainController::delete_
```php
$router->delete('delete/:id', [MainController::class, 'delete']);
```
_call / вызывает MainController::any 'GET|POST|PUT|PATCH|DELETE'_
```php
$router->any('any/:id', [MainController::class, 'any']);
```
_call / вызывает MainController::read для GET_

_call / вызывает MainController::create для POST_

_call / вызывает MainController::update для PUT_

_call / вызывает MainController::delete для DELETE_
```php
$router->resource('api/:id', MainController::class);
```
Изменить методы контроллера по умолчанию можно передав массив с вашими именами\
You can change the default controller methods by passing an array with your names
```php
$router->resource('api/:id', MainController::class, ['actionIndex', 'actionAdd', 'actionUpdate', 'actionDrop']);
```
#### A variant of declaring a route using the set method / Вариант объявления маршрута методом set
_call / вызывает MainController::actionIndex_
```php
$router->set(['/test/:id', 'DELETE|PUT', [MainController::class, 'actionIndex'], [
        'before' => [First::class, Second::class],
        'after'  => [[First::class], [Second::class]]
]]);
```
_Exemple / Пример Middleware_
```php
/**
 * Handles requests as a middleware using __invoke().
 */
class SomeMiddleware
{
    public function __invoke($next, ...$params)
    {
        // Logic here

        if ($next) {
            $next();
        }
    }
}
```
