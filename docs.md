## Table of contents
- [Rudra\Router\Router](#rudra_router_router)
- [Rudra\Router\RouterFacade](#rudra_router_routerfacade)
- [Rudra\Router\RouterInterface](#rudra_router_routerinterface)
- [Rudra\Router\Routing](#rudra_router_routing)
- [Rudra\Router\Traits\RouterAnnotationTrait](#rudra_router_traits_routerannotationtrait)
- [Rudra\Router\Traits\RouterRequestMethodTrait](#rudra_router_traits_routerrequestmethodtrait)
<hr>

<a id="rudra_router_router"></a>

### Class: Rudra\Router\Router
| Visibility | Function |
|:-----------|:---------|
| public | `set(array $route): void`<br>Sets the route, parsing HTTP methods (if multiple are specified via \|).<br>Registers a route handler for each method.<br>-------------------------<br>Устанавливает маршрут, разбирая HTTP-методы (если указано несколько через \|).<br>Для каждого метода регистрирует обработчик маршрута. |
| private | `handleRequestUri(array $route): void`<br>Processes the incoming URI request and checks if it matches the current route.<br>-------------------------<br>Обрабатывает входящий URI-запрос и проверяет его совпадение с текущим маршрутом. |
| private | `handleRequestMethod(): void`<br>Processes the HTTP request method, including spoofing via _method (for PUT/PATCH/DELETE)<br>-------------------------<br>Обрабатывает HTTP-метод запроса, включая spoofing через _method (для PUT/PATCH/DELETE) |
| private | `handlePattern(array $route, array $request): array`<br>Matches the URI from the route with the actual request, processing parameters of the form :param and :regexp.<br>This method is used to extract dynamic segments from a URI pattern:<br>-------------------------<br>Сопоставляет URI из маршрута с фактическим запросом, обрабатывая параметры вида :param и :regexp.<br>Метод извлекает динамические сегменты из URL-шаблона: |
| private | `setCallable(array $route, ?array $params): void`<br>Calls the controller associated with the route — either a Closure or a controller method.<br>-------------------------<br>Вызывает контроллер, связанный с маршрутом — либо Closure, либо метод контроллера. |
| public | `directCall(array $route, ?array $params): void`<br>Calls the controller and its method directly, performing the full lifecycle:<br>This method is used to fully dispatch a route after matching it with the current request.<br>-------------------------<br>Вызывает контроллер и его метод напрямую, выполняя полный жизненный цикл:<br>Метод используется для полной диспетчеризации маршрута после его совпадения с текущим запросом. |
| private | `callActionThroughReflection(?array $params, string $action, object $controller): void`<br>Calls the controller method using Reflection, performing automatic parameter injection based on type hints.<br>This method is typically used when the zend.exception_ignore_args setting is enabled,<br>allowing for more flexible and type-safe dependency resolution.<br>-------------------------<br>Вызывает метод контроллера с помощью Reflection, выполняя автоматическое внедрение параметров на основе типизации.<br>Этот метод обычно используется, когда включена настройка zend.exception_ignore_args,<br>что позволяет более гибко и безопасно разрешать зависимости по типам. |
| private | `callActionThroughException(?array $params, string $action, object $controller): void`<br>Calls the specified controller method directly.<br>If the argument type or number does not match — tries to automatically inject required dependencies.<br>This is a fallback mechanism for cases where Reflection-based injection is disabled or unavailable.<br>Handles two types of errors during invocation:<br>- \ArgumentCountError — thrown when the number of arguments doesn't match the method signature.<br>- \TypeError — thrown when an argument is not compatible with the expected type.<br>In both cases, Rudra's autowire system attempts to resolve and inject the correct dependencies.<br>-------------------------<br>Вызывает указанный метод контроллера напрямую.<br>Если тип или количество аргументов не совпадает — пытается автоматически внедрить нужные зависимости.<br>Это механизм отката, используемый, когда недоступен вызов через Reflection.<br>Обрабатываются следующие ошибки:<br>- \ArgumentCountError — выбрасывается, если количество аргументов не совпадает с ожидаемым.<br>- \TypeError — выбрасывается, если тип аргумента не соответствует ожидаемому.<br>В обоих случаях система автовайринга Rudra пытается разрешить и внедрить правильные зависимости. |
| public | `handleMiddleware(array $chain): void`<br>Executes a chain of middleware, recursively calling each element.<br>Middleware can be specified in one of the supported formats:<br>- 'MiddlewareClass' (string) — a simple class name to call without parameters.<br>- ['MiddlewareClass'] (array with class name) — same as above, allows for future extensions.<br>- ['MiddlewareClass', \$parameter] (array with class and parameter) — passes the parameter to the middleware.<br>Each middleware must implement the __invoke() method to be callable.<br>--------------------<br>Выполняет цепочку middleware, рекурсивно вызывая каждый элемент.<br>Middleware может быть указан в одном из поддерживаемых форматов:<br>- 'MiddlewareClass' (строка) — простое имя класса без параметров.<br>- ['MiddlewareClass'] (массив с именем класса) — аналогично предыдущему, удобно для расширения.<br>- ['MiddlewareClass', \$parameter] (массив с классом и параметром) — передаёт параметр в middleware.<br>Каждый middleware должен реализовывать метод __invoke(), чтобы быть вызываемым. |
| public | `annotationCollector(array $controllers, bool $getter, bool $attributes): ?array`<br>Collects and processes annotations from the specified controllers.<br>This method scans each controller class for Routing and Middleware annotations,<br>builds route definitions based on those annotations, and either:<br>- Registers them directly via `set()` (if \$getter = false), or<br>- Returns them as an array (if \$getter = true).<br>--------------------<br>Собирает и обрабатывает аннотации указанных контроллеров.<br>Метод сканирует каждый контроллер на наличие аннотаций Routing и Middleware,<br>формирует определения маршрутов и либо:<br>- Регистрирует их напрямую через `set()` (если \$getter = false),<br>- Возвращает как массив (если \$getter = true). |
| protected | `handleAnnotationMiddleware(array $annotation): array`<br>Processes middleware annotations into a valid middleware format.<br>--------------------<br>Обрабатывает аннотации middleware в поддерживаемый формат.<br>```#[Middleware(name: "Auth", params: "admin")]```<br>в:<br>```['Auth', 'admin']``` |
| public | `__construct(Rudra\Container\Interfaces\RudraInterface $rudra)`<br> |
| public | `rudra(): Rudra\Container\Interfaces\RudraInterface`<br> |
| public | `get(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the GET HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода GET. |
| public | `post(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the POST HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода POST. |
| public | `put(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PUT HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода PUT. |
| public | `patch(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PATCH HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода PATCH. |
| public | `delete(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the DELETE HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода DELETE. |
| public | `any(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route that supports all HTTP methods.<br>Sets the method to a pipe-separated string ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>allowing the same route to handle multiple request types.<br>--------------------<br>Регистрирует маршрут, поддерживающий все HTTP-методы.<br>Устанавливает метод как строку с разделителем \| ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>что позволяет использовать один маршрут для нескольких типов запросов. |
| public | `resource(string $pattern, string $controller, array $actions): void`<br>Registers a resource route, mapping standard actions to controller methods.<br>Supports common CRUD operations by default:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Can be customized with an optional \$actions array.<br>--------------------<br>Регистрирует ресурсный маршрут, связывая стандартные действия с методами контроллера.<br>По умолчанию поддерживает CRUD-операции:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Может быть переопределён с помощью массива \$actions. |
| protected | `setRoute(string $pattern,  $target, string $httpMethod, array $middleware): void`<br>The method constructs a route definition and passes it to the `set()` method for registration.<br>--------------------<br>Метод формирует определение маршрута и передает его в метод `set()` для регистрации. |


<a id="rudra_router_routerfacade"></a>

### Class: Rudra\Router\RouterFacade
| Visibility | Function |
|:-----------|:---------|
| public static | `__callStatic(string $method, array $parameters): ?mixed`<br> |


<a id="rudra_router_routerinterface"></a>

### Class: Rudra\Router\RouterInterface
| Visibility | Function |
|:-----------|:---------|
| abstract public | `set(array $route): void`<br>Sets the route, parsing HTTP methods (if multiple are specified via \|).<br>Registers a route handler for each method.<br>-------------------------<br>Устанавливает маршрут, разбирая HTTP-методы (если указано несколько через \|).<br>Для каждого метода регистрирует обработчик маршрута. |
| abstract public | `directCall(array $route, ?array $params): void`<br>Calls the controller and its method directly, performing the full lifecycle:<br>This method is used to fully dispatch a route after matching it with the current request.<br>-------------------------<br>Вызывает контроллер и его метод напрямую, выполняя полный жизненный цикл:<br>Метод используется для полной диспетчеризации маршрута после его совпадения с текущим запросом. |


<a id="rudra_router_routing"></a>

### Class: Rudra\Router\Routing
| Visibility | Function |
|:-----------|:---------|


<a id="rudra_router_traits_routerannotationtrait"></a>

### Class: Rudra\Router\Traits\RouterAnnotationTrait
| Visibility | Function |
|:-----------|:---------|
| public | `annotationCollector(array $controllers, bool $getter, bool $attributes): ?array`<br>Collects and processes annotations from the specified controllers.<br>This method scans each controller class for Routing and Middleware annotations,<br>builds route definitions based on those annotations, and either:<br>- Registers them directly via `set()` (if \$getter = false), or<br>- Returns them as an array (if \$getter = true).<br>--------------------<br>Собирает и обрабатывает аннотации указанных контроллеров.<br>Метод сканирует каждый контроллер на наличие аннотаций Routing и Middleware,<br>формирует определения маршрутов и либо:<br>- Регистрирует их напрямую через `set()` (если \$getter = false),<br>- Возвращает как массив (если \$getter = true). |
| protected | `handleAnnotationMiddleware(array $annotation): array`<br>Processes middleware annotations into a valid middleware format.<br>--------------------<br>Обрабатывает аннотации middleware в поддерживаемый формат.<br>```#[Middleware(name: "Auth", params: "admin")]```<br>в:<br>```['Auth', 'admin']``` |


<a id="rudra_router_traits_routerrequestmethodtrait"></a>

### Class: Rudra\Router\Traits\RouterRequestMethodTrait
| Visibility | Function |
|:-----------|:---------|
| public | `get(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the GET HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода GET. |
| public | `post(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the POST HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода POST. |
| public | `put(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PUT HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода PUT. |
| public | `patch(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the PATCH HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода PATCH. |
| public | `delete(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route with the DELETE HTTP method.<br>--------------------<br>Регистрирует маршрут с использованием метода DELETE. |
| public | `any(string $pattern, callable\|array $target, array $middleware): void`<br>Registers a route that supports all HTTP methods.<br>Sets the method to a pipe-separated string ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>allowing the same route to handle multiple request types.<br>--------------------<br>Регистрирует маршрут, поддерживающий все HTTP-методы.<br>Устанавливает метод как строку с разделителем \| ('GET\|POST\|PUT\|PATCH\|DELETE'),<br>что позволяет использовать один маршрут для нескольких типов запросов. |
| public | `resource(string $pattern, string $controller, array $actions): void`<br>Registers a resource route, mapping standard actions to controller methods.<br>Supports common CRUD operations by default:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Can be customized with an optional \$actions array.<br>--------------------<br>Регистрирует ресурсный маршрут, связывая стандартные действия с методами контроллера.<br>По умолчанию поддерживает CRUD-операции:<br>- GET=> read<br>- POST => create<br>- PUT=> update<br>- DELETE => delete<br>Может быть переопределён с помощью массива \$actions. |
| protected | `setRoute(string $pattern,  $target, string $httpMethod, array $middleware): void`<br>The method constructs a route definition and passes it to the `set()` method for registration.<br>--------------------<br>Метод формирует определение маршрута и передает его в метод `set()` для регистрации. |
<hr>

###### created with [Rudra-Documentation-Collector](#https://github.com/Jagepard/Rudra-Documentation-Collector)
