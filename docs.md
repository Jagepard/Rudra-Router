## Table of contents
- [Rudra\Router\Attributes\Middleware](#rudra_router_attributes_middleware)
- [Rudra\Router\Attributes\Routing](#rudra_router_attributes_routing)
- [Rudra\Router\Router](#rudra_router_router)
- [Rudra\Router\RouterFacade](#rudra_router_routerfacade)
- [Rudra\Router\RouterInterface](#rudra_router_routerinterface)
- [Rudra\Router\Traits\RouterAnnotationTrait](#rudra_router_traits_routerannotationtrait)
- [Rudra\Router\Traits\RouterRequestMethodTrait](#rudra_router_traits_routerrequestmethodtrait)


---



<a id="rudra_router_attributes_middleware"></a>

### Class: Rudra\Router\Attributes\Middleware
| Visibility | Function |
|:-----------|:---------|
| public | `__construct(string $name, ?string $params)`<br> |


<a id="rudra_router_attributes_routing"></a>

### Class: Rudra\Router\Attributes\Routing
| Visibility | Function |
|:-----------|:---------|
| public | `__construct(string $url, array\|string $method)`<br> |


<a id="rudra_router_router"></a>

### Class: Rudra\Router\Router
| Visibility | Function |
|:-----------|:---------|
| public | `set(array $route): void`<br>Sets the route, parsing HTTP methods (if multiple are specified via \|).<br>Registers a route handler for each method. |
| private | `handleRequestUri(array $route): void`<br>Processes the incoming URI request and checks if it matches the current route. |
| private | `handleRequestMethod(): void`<br>Processes the HTTP request method, including spoofing via _method (for PUT/PATCH/DELETE) |
| private | `handlePattern(array $route, array $request): array`<br>Matches the URI from the route with the actual request, processing parameters of the form :param and :regexp.<br>This method is used to extract dynamic segments from a URI pattern |
| private | `setCallable(array $route, ?array $params): void`<br>Calls the controller associated with the route — either a Closure or a controller method. |
| public | `directCall(array $route, ?array $params): void`<br>Calls the controller and its method directly, performing the full lifecycle:<br>This method is used to fully dispatch a route after matching it with the current request. |
| private | `callActionThroughReflection(?array $params, string $action, object $controller): void`<br>Calls the controller method using Reflection, performing automatic parameter injection based on type hints.<br>This method is typically used when the zend.exception_ignore_args setting is enabled,<br>allowing for more flexible and type-safe dependency resolution. |
| private | `callActionThroughException(?array $params, string $action, object $controller): void`<br>Calls the specified controller method directly.<br>If the argument type or number does not match — tries to automatically inject required dependencies.<br>This is a fallback mechanism for cases where Reflection-based injection is disabled or unavailable.<br>Handles two types of errors during invocation:<br>- \ArgumentCountError — thrown when the number of arguments doesn't match the method signature.<br>- \TypeError — thrown when an argument is not compatible with the expected type.<br>In both cases, Rudra's autowire system attempts to resolve and inject the correct dependencies. |
| public | `handleMiddleware(array $chain): void`<br>Executes a chain of middleware, recursively calling each element.<br>Middleware can be specified in one of the supported formats:<br>- 'MiddlewareClass' (string) — a simple class name to call without parameters.<br>- ['MiddlewareClass'] (array with class name) — same as above, allows for future extensions.<br>- ['MiddlewareClass', \$parameter] (array with class and parameter) — passes the parameter to the middleware.<br>Each middleware must implement the __invoke() method to be callable. |
| public | `annotationCollector(array $controllers, bool $getter, bool $attributes): ?array`<br>Collects and processes annotations from the specified controllers.<br>This method scans each controller class for Routing and Middleware annotations,<br>builds route definitions based on those annotations, and either:<br>- Registers them directly via `set()` (if \$getter = false), or<br>- Returns them as an array (if \$getter = true). |
| protected | `handleAnnotationMiddleware(array $annotation): array`<br>Processes middleware annotations into a valid middleware format.<br>```#[Middleware(name: "Auth", params: "admin")]```<br>to:<br>```['Auth', 'admin']``` |
| public | `__construct(Rudra\Container\Interfaces\RudraInterface $rudra)`<br> |
| public | `rudra(): Rudra\Container\Interfaces\RudraInterface`<br> |
| public | `get(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the GET HTTP method. |
| public | `post(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the POST HTTP method. |
| public | `put(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PUT HTTP method. |
| public | `patch(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PATCH HTTP method. |
| public | `delete(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the DELETE HTTP method. |
| public | `any(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route that supports all HTTP methods.<br>Sets the method to a pipe-separated string ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>allowing the same route to handle multiple request types. |
| public | `resource(string $pattern, string $controller, array $actions): void`<br>Registers a resource route, mapping standard actions to controller methods.<br>Supports common CRUD operations by default:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Can be customized with an optional \$actions array. |
| protected | `setRoute(string $pattern, $target, string $httpMethod, array $middleware): void`<br>The method constructs a route definition and passes it to the `set()` method for registration. |


<a id="rudra_router_routerfacade"></a>

### Class: Rudra\Router\RouterFacade
| Visibility | Function |
|:-----------|:---------|
| public static | `__callStatic(string $method, array $parameters): mixed`<br>Handles static method calls for the Facade class<br>It dynamically resolves the underlying class name by removing "Facade" from the class name<br>If the resolved class does not exist, it attempts to clean up the class name by removing spaces<br>If the resolved class is not already registered in the container, it registers it<br>Finally, it delegates the static method call to the resolved class instance |


<a id="rudra_router_routerinterface"></a>

### Class: Rudra\Router\RouterInterface
| Visibility | Function |
|:-----------|:---------|
| abstract public | `set(array $route): void`<br> |
| abstract public | `directCall(array $route, ?array $params): void`<br> |


<a id="rudra_router_traits_routerannotationtrait"></a>

### Class: Rudra\Router\Traits\RouterAnnotationTrait
| Visibility | Function |
|:-----------|:---------|
| public | `annotationCollector(array $controllers, bool $getter, bool $attributes): ?array`<br>Collects and processes annotations from the specified controllers.<br>This method scans each controller class for Routing and Middleware annotations,<br>builds route definitions based on those annotations, and either:<br>- Registers them directly via `set()` (if \$getter = false), or<br>- Returns them as an array (if \$getter = true). |
| protected | `handleAnnotationMiddleware(array $annotation): array`<br>Processes middleware annotations into a valid middleware format.<br>```#[Middleware(name: "Auth", params: "admin")]```<br>to:<br>```['Auth', 'admin']``` |


<a id="rudra_router_traits_routerrequestmethodtrait"></a>

### Class: Rudra\Router\Traits\RouterRequestMethodTrait
| Visibility | Function |
|:-----------|:---------|
| public | `get(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the GET HTTP method. |
| public | `post(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the POST HTTP method. |
| public | `put(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PUT HTTP method. |
| public | `patch(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PATCH HTTP method. |
| public | `delete(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the DELETE HTTP method. |
| public | `any(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route that supports all HTTP methods.<br>Sets the method to a pipe-separated string ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>allowing the same route to handle multiple request types. |
| public | `resource(string $pattern, string $controller, array $actions): void`<br>Registers a resource route, mapping standard actions to controller methods.<br>Supports common CRUD operations by default:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Can be customized with an optional \$actions array. |
| protected | `setRoute(string $pattern, $target, string $httpMethod, array $middleware): void`<br>The method constructs a route definition and passes it to the `set()` method for registration. |


---

###### created with [Rudra-Documentation-Collector](https://github.com/Jagepard/Rudra-Documentation-Collector)
