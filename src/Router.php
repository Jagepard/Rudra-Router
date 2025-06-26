<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use ReflectionException;
use Rudra\Container\Traits\SetRudraContainersTrait;
use Rudra\Exceptions\{MiddlewareException, RouterException};
use Rudra\Router\Traits\{RouterAnnotationTrait, RouterRequestMethodTrait};

class Router implements RouterInterface
{
    use RouterAnnotationTrait;
    use SetRudraContainersTrait;
    use RouterRequestMethodTrait;

    private array $reflectionCache = [];

    /**
     * Sets the route, parsing HTTP methods (if multiple are specified via |).
     * Registers a route handler for each method.
     * -------------------------
     * Устанавливает маршрут, разбирая HTTP-методы (если указано несколько через |).
     * Для каждого метода регистрирует обработчик маршрута.
     *
     * @param array $route
     * @return void
     */
    public function set(array $route): void
    {
        $httpMethods = str_contains($route['method'], '|')
            ? explode('|', $route['method'])
            : [$route['method']];

        foreach ($httpMethods as $httpMethod) {
            $route['method'] = $httpMethod;
            $this->handleRequestUri($route);
        }
    }

    /**
     * Processes the incoming URI request and checks if it matches the current route.
     * -------------------------
     * Обрабатывает входящий URI-запрос и проверяет его совпадение с текущим маршрутом.
     *
     * @param array $route
     * @return void
     */
    private function handleRequestUri(array $route): void
    {
        $this->handleRequestMethod();

        $request = $this->rudra->request();
        $server = $request->server();

        if ($route['method'] !== $server->get('REQUEST_METHOD')) {
            return;
        }

        $uriRaw = $server->get('REQUEST_URI');
        $parsed = parse_url($uriRaw);
        $requestPath = $parsed && isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';
        $uriSegments = explode('/', $requestPath);
        [$uri, $params] = $this->handlePattern($route, $uriSegments);

        if ($uri === $uriSegments) {
            $this->setCallable($route, $params);
        }
    }

    /**
     * Processes the HTTP request method, including spoofing via _method (for PUT/PATCH/DELETE)
     * -------------------------
     * Обрабатывает HTTP-метод запроса, включая spoofing через _method (для PUT/PATCH/DELETE)
     *
     * @return void
     */
    private function handleRequestMethod(): void
    {
        $request = $this->rudra->request();
        $requestMethod = $request->server()->get('REQUEST_METHOD');

        // Spoofing the method via _method parameter in POST requests
        if ($requestMethod === 'POST' && $request->post()->has('_method')) {
            $spoofedMethod = strtoupper($request->post()->get('_method'));
            if (in_array($spoofedMethod, ['PUT', 'PATCH', 'DELETE'])) {
                $requestMethod = $spoofedMethod;
                $request->server()->set(['REQUEST_METHOD' => $spoofedMethod]);
            }
        }

        // Handle PUT, PATCH, and DELETE requests by parsing raw input data
        if (in_array($requestMethod, ['PUT', 'PATCH', 'DELETE'])) {
            $rawInput = file_get_contents('php://input');
            parse_str($rawInput, $data);
            $request->{strtolower($requestMethod)}()->set($data);
        }
    }

    /**
     * Matches the URI from the route with the actual request, processing parameters of the form :param and :regexp.
     * This method is used to extract dynamic segments from a URI pattern:
     * -------------------------
     * Сопоставляет URI из маршрута с фактическим запросом, обрабатывая параметры вида :param и :regexp.
     * Метод извлекает динамические сегменты из URL-шаблона:
     * 
     * @param array $route
     * @param array $request
     * @return array
     */
    private function handlePattern(array $route, array $request): array
    {
        $uri = [];
        $params = null;
        $subject = explode('/', ltrim($route['url'], '/'));
        $count = count($subject);

        for ($i = 0; $i < $count; $i++) {
            if (preg_match("/^:[a-zA-Z0-9_-]+$/", $subject[$i]) > 0 && array_key_exists($i, $request)) {
                $value = $request[$i];
                $uri[] = $value;
                $params[] = $value;
                continue;
            }
            
            if (preg_match("/^:([\\[\\]\\\\:a-zA-Z0-9_\\-{,}]+)$/", $subject[$i], $matches)) {
                if (array_key_exists($i, $request)) {
                    $pattern = $matches[1];
                    if (preg_match("/^$pattern$/", $request[$i])) {
                        $uri[] = $request[$i];
                        $params[] = $request[$i];
                    } else {
                        $uri[] = '!@#$%^&*';
                    }
                }

                continue;
            }

            $uri[] = $subject[$i];
        }

        return [$uri, $params];
    }

    /**
     * Calls the controller associated with the route — either a Closure or a controller method.
     * -------------------------
     * Вызывает контроллер, связанный с маршрутом — либо Closure, либо метод контроллера.
     *
     * @param array $route
     * @param array|null $params
     * @return void
     */
    private function setCallable(array $route, ?array $params): void
    {
        if ($route['controller'] instanceof \Closure) {
            if (is_array($params)) {
                $route['controller'](...$params);
            } else {
                $route['controller']($params);
            }

            if ($this->rudra->config()->get('environment') !== 'test') {
                exit();
            }

            return;
        }

        $this->directCall($route, $params);
    }

    /**
     * Calls the controller and its method directly, performing the full lifecycle:
     * This method is used to fully dispatch a route after matching it with the current request.
     * -------------------------
     * Вызывает контроллер и его метод напрямую, выполняя полный жизненный цикл:
     * Метод используется для полной диспетчеризации маршрута после его совпадения с текущим запросом.
     *
     * @param array $route
     * @param array|null $params
     * @return void
     * @throws RouterException
     */
    public function directCall(array $route, ?array $params = null): void
    {
        $controller = $this->rudra->get($route['controller']);
        $action = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new RouterException("503");
        }

        $controller->shipInit();
        $controller->containerInit();
        $controller->init();

        $controller->before();

        if (isset($route['middleware']['before'])) {
            $this->handleMiddleware($route['middleware']['before']);
        }

        ((int) ini_get('zend.exception_ignore_args') === 1)
            ? $this->callActionThroughReflection($params, $action, $controller)
            : $this->callActionThroughException($params, $action, $controller);

        if (isset($route['middleware']['after'])) {
            $this->handleMiddleware($route['middleware']['after']);
        }

        $controller->after();

        if ($this->rudra->config()->get('environment') !== 'test') {
            exit();
        }
    }

    /**
     * Calls the controller method using Reflection, performing automatic parameter injection based on type hints.
     *
     * This method is typically used when the zend.exception_ignore_args setting is enabled,
     * allowing for more flexible and type-safe dependency resolution.
     * -------------------------
     * Вызывает метод контроллера с помощью Reflection, выполняя автоматическое внедрение параметров на основе типизации.
     *
     * Этот метод обычно используется, когда включена настройка zend.exception_ignore_args,
     * что позволяет более гибко и безопасно разрешать зависимости по типам.
     * 
     * @param array|null $params
     * @param string action
     * @param object $controller
     * @return void
     * @throws RouterException
     */
    private function callActionThroughReflection(?array $params, string $action, object $controller): void
    {
        if ($params && in_array('', $params, true)) {
            throw new RouterException("404");
        }

        $cacheKey = get_class($controller) . "::$action";

        if (!isset($this->reflectionCache[$cacheKey])) {
            $this->reflectionCache[$cacheKey] = [
                'method' => new \ReflectionMethod($controller, $action),
            ];
        }

        $method = $this->reflectionCache[$cacheKey]['method'];
        $arguments = $this->rudra()->getParamsIoC($method, $params);
        $method->invokeArgs($controller, $arguments);
    }

    /**
     * Calls the specified controller method directly.
     *
     * If the argument type or number does not match — tries to automatically inject required dependencies.
     * This is a fallback mechanism for cases where Reflection-based injection is disabled or unavailable.
     * 
     * Handles two types of errors during invocation:
     * - \ArgumentCountError — thrown when the number of arguments doesn't match the method signature.
     * - \TypeError — thrown when an argument is not compatible with the expected type.
     *
     * In both cases, Rudra's autowire system attempts to resolve and inject the correct dependencies.
     * -------------------------
     * Вызывает указанный метод контроллера напрямую.
     *
     * Если тип или количество аргументов не совпадает — пытается автоматически внедрить нужные зависимости.
     * Это механизм отката, используемый, когда недоступен вызов через Reflection.
     *
     * Обрабатываются следующие ошибки:
     * - \ArgumentCountError — выбрасывается, если количество аргументов не совпадает с ожидаемым.
     * - \TypeError — выбрасывается, если тип аргумента не соответствует ожидаемому.
     *
     * В обоих случаях система автовайринга Rudra пытается разрешить и внедрить правильные зависимости.
     *
     * @param array|null $params
     * @param string $action
     * @param object $controller
     * @return void
     * @throws RouterException
     */
    private function callActionThroughException(?array $params, string $action, object $controller): void
    {
        if (isset($params) && in_array('', $params)) {
            throw new RouterException("404");
        }

        try {
            if (empty($params)) {
                $controller->$action();
            } else {
                $controller->$action(...$params);
            }
        } catch (\ArgumentCountError $e) {
            $trace = $e->getTrace()[0];
            $this->rudra()->autowire($this->rudra()->get($trace['class']), $trace['function']);
        } catch (\TypeError $e) {
            $trace = $e->getTrace()[0];
            $this->rudra()->autowire($this->rudra()->new($trace['class']), $trace['function'], $trace['args']);
        }
    }

    /**
     * Executes a chain of middleware, recursively calling each element.
     *
     * Middleware can be specified in one of the supported formats:
     * - 'MiddlewareClass' (string) — a simple class name to call without parameters.
     * - ['MiddlewareClass'] (array with class name) — same as above, allows for future extensions.
     * - ['MiddlewareClass', $parameter] (array with class and parameter) — passes the parameter to the middleware.
     *
     * Each middleware must implement the __invoke() method to be callable.
     * --------------------
     * Выполняет цепочку middleware, рекурсивно вызывая каждый элемент.
     *
     * Middleware может быть указан в одном из поддерживаемых форматов:
     * - 'MiddlewareClass' (строка) — простое имя класса без параметров.
     * - ['MiddlewareClass'] (массив с именем класса) — аналогично предыдущему, удобно для расширения.
     * - ['MiddlewareClass', $parameter] (массив с классом и параметром) — передаёт параметр в middleware.
     *
     * Каждый middleware должен реализовывать метод __invoke(), чтобы быть вызываемым.
     *
     * @param array $chain
     * @return void
     * @throws \Rudra\Router\Exceptions\MiddlewareException
     */
    public function handleMiddleware(array $chain): void
    {
        if (!$chain) {
            return;
        }

        $current = array_shift($chain);

        try {
            if (is_array($current) && count($current) === 2 && is_string($current[0])) {
                $middleware = new $current[0]();
                $middleware($chain, ...$current[1]);
                return;
            }

            if (is_array($current) && is_string($current[0])) {
                $middleware = new $current[0]();
                $middleware($chain);
                return;
            }

            if (is_string($current)) {
                $middleware = new $current();
                $middleware($chain);
                return;
            }

            if (is_callable($current)) {
                $current($chain);
                return;
            }

            throw new MiddlewareException('Invalid middleware format');
        } catch (\Throwable $e) {
            throw new MiddlewareException("Failed to process middleware: " . json_encode($current), 0, $e);
        }
    }
}
