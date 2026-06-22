[![PHPunit](https://github.com/Jagepard/Rudra-Router/actions/workflows/php.yml/badge.svg)](https://github.com/Jagepard/Rudra-Router/actions/workflows/php.yml)
[![Maintainability](https://qlty.sh/badges/d9252114-5cc4-405e-bbf7-6419ec50266f/maintainability.svg)](https://qlty.sh/gh/Jagepard/projects/Rudra-Router)
[![CodeFactor](https://www.codefactor.io/repository/github/jagepard/rudra-router/badge)](https://www.codefactor.io/repository/github/jagepard/rudra-router)
[![Coverage Status](https://coveralls.io/repos/github/Jagepard/Rudra-Router/badge.svg?branch=master)](https://coveralls.io/github/Jagepard/Rudra-Router?branch=master)

-----

# Rudra-Router

A lightweight and transparent HTTP router for PHP. Supports dynamic parameters, regular expressions, middleware, and RESTful resources.

## Features

- Dynamic URL parameters (`:name`) and regular expressions (`:[\d]{1,3}`)
- All HTTP methods supported: GET, POST, PUT, PATCH, DELETE
- Method spoofing via `_method` (for PUT/PATCH/DELETE from POST requests)
- Middleware executed before and after controller execution
- RESTful resources in a single line
- Automatic dependency injection via IoC container
- Works via instance or Facade

## Installation

```bash
composer require rudra/router
```

## Basic Usage

### Initialization

```php
use Rudra\Router\Router;
use Rudra\Container\Rudra;

$router = new Router(Rudra::run());
```

### Facade Usage

```php
use Rudra\Container\Facades\Rudra;  
use Rudra\Router\RouterFacade as Router;
use Rudra\Container\Interfaces\RudraInterface;

Rudra::binding()->set([RudraInterface::class => Rudra::run()]);
```

## Route Definition

### Simple Routes with Closure

```php
$router->get('hello/:name', function ($name) {
    echo "Hello $name!";
});
```

### With Regular Expressions

```php
$router->get('user/:[\d]{1,3}', function ($id) {
    echo "User ID: $id";
});
```

### Controller Method Call

```php
$router->get('read/:id', [MainController::class, 'read']);
```

### Via Facade

```php
Router::get('callback/:name', function ($name) {
    echo "Hello $name!";
});

Router::get('read/:id', [MainController::class, 'read']);
```

## HTTP Methods

```php
$router->get('read/:id',      [MainController::class, 'read']);
$router->post('create/:id',   [MainController::class, 'create']);
$router->put('update/:id',    [MainController::class, 'update']);
$router->patch('patch/:id',   [MainController::class, 'patch']);
$router->delete('delete/:id', [MainController::class, 'delete']);
```

### Any Method (GET|POST|PUT|PATCH|DELETE)

```php
$router->any('any/:id', [MainController::class, 'any']);
```

## Middleware

Middleware runs before (`before`) and after (`after`) the controller. Each middleware must implement the `__invoke()` method.

### Basic Setup

```php
$router->get('read/page', [MainController::class, 'read'], [
    'before' => [AuthMiddleware::class],
    'after'  => [LogMiddleware::class]
]);
```

### Middleware with Parameters

```php
$router->get('admin/:id', [AdminController::class, 'show'], [
    'before' => [
        AuthMiddleware::class,
        [RoleMiddleware::class, ['role' => 'admin', new PermissionChecker()]]
    ],
    'after' => [
        LogMiddleware::class,
        [CacheMiddleware::class, ['ttl' => 3600]]
    ]
]);
```

### Middleware Example

```php
use Rudra\Router\RouterFacade as Router;

class AuthMiddleware
{
    public function __invoke($next, ...$params)
    {
        // Logic before controller
        if (!Auth::check()) {
            throw new UnauthorizedException();
        }

        // Pass control to the next middleware in chain
        if ($next) {
            Router::handleMiddleware($next);
        }

        // Logic after controller (optional)
    }
}
```

Simple middleware without chain:

```php
class UnsetSessionMiddleware
{
    public function __invoke($next, ...$params)
    {
        Session::remove('value');
        Session::remove('alert');
        Session::remove('errors');

        if ($next) {
            Router::handleMiddleware($next);
        }
    }
}
```
## RESTful Resources

A single line registers all standard CRUD routes:

```php
$router->resource('api/users', UserController::class);
```

This creates the following routes:

| Method | URL       | Controller Method |
|--------|-----------|-------------------|
| GET    | api/users | read              |
| POST   | api/users | create            |
| PUT    | api/users | update            |
| PATCH  | api/users | update            |
| DELETE | api/users | delete            |

### Custom Method Names

```php
$router->resource('api/posts', PostController::class, [
    'actionIndex',
    'actionAdd',
    'actionUpdate',
    'actionDrop'
]);
```
## The set() Method — Extended Syntax

Allows defining a route with multiple HTTP methods via `|`:

```php
$router->set([
    'url'        => '/api/users/:id',
    'method'     => 'GET|POST',
    'controller' => [UserController::class, 'handle'],
    'middleware' => [
        'before' => [AuthMiddleware::class],
        'after'  => [LogMiddleware::class]
    ]
]);
```

## Controller Lifecycle

When a controller method is invoked, the following stages are executed:

1. `shipInit()` — base component initialization
2. `containerInit()` — container initialization
3. `init()` — user-defined initialization
4. `before()` — hook before middleware
5. **`before` middleware**
6. **Action method call** (with automatic dependency injection)
7. **`after` middleware**
8. `after()` — hook after middleware

## Automatic Dependency Injection

Action method parameters are automatically resolved via the IoC container:

```php
class UserController extends Controller
{
    public function show(int $id, Request $request, UserService $service)
    {
        // $id — from URL
        // $request and $service — injected automatically
    }
}
```

## Method Spoofing

For forms that do not support PUT/PATCH/DELETE, use the `_method` parameter:

```html
<form method="POST" action="/users/123">
    <input type="hidden" name="_method" value="PUT">
    <!-- form fields -->
</form>
```

The router automatically recognizes this as a PUT request.

## Error Handling

- `RouterException("Not Found", 404)` — route not found or parameters do not match
- `RouterException("Service Unavailable", 503)` — controller method does not exist
- `MiddlewareException` — error in the middleware chain

## License

This project is licensed under the **Mozilla Public License 2.0 (MPL-2.0)** — a free, open-source license that:

- Requires preservation of copyright and license notices,
- Allows commercial and non-commercial use,
- Requires that any modifications to the original files remain open under MPL-2.0,
- Permits combining with proprietary code in larger works.

📄 Full license text: [LICENSE](./LICENSE)  
🌐 Official MPL-2.0 page: https://mozilla.org/MPL/2.0/