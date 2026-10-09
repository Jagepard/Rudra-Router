<?php declare(strict_types=1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Router;

use Rudra\Container\Traits\SetRudraContainersTrait;
use Rudra\Exceptions\MiddlewareException;
use Rudra\Exceptions\RouterException;
use Rudra\Router\Traits\RouterAnnotationTrait;
use Rudra\Router\Traits\RouterRequestMethodTrait;

class Router implements RouterInterface
{
    use RouterAnnotationTrait;
    use SetRudraContainersTrait;
    use RouterRequestMethodTrait;

    private array $reflectionCache = [];

    /**
     * Performance optimization caches
     */
    private bool $isMethodSpoofed = false;        // Cache spoofing - run only once per request
    private ?array $cachedUriSegments = null;     // Cache parsed URI segments
    private array $patternCache = [];             // Cache regex validation results
    private $cachedRequest = null;                // Cache request object
    private $cachedServer = null;                 // Cache server object

    /**
     * Sets the route, parsing HTTP methods (if multiple are specified via |).
     * Registers a route handler for each method.
     */
    #[\Override]
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
     */
    private function handleRequestUri(array $route): void
    {
        $server = $this->getServer();

        $this->spoofRequestMethod();

        if ($route['method'] !== $server->get('REQUEST_METHOD')) {
            return;
        }

        $this->parseRequestBody();

        $uriSegments = $this->getRequestUriSegments();
        [$uri, $params] = $this->handlePattern($route, $uriSegments);

        if ($uri === $uriSegments) {
            $this->setCallable($route, $params);
        }
    }

    /**
     * Returns cached request object to avoid repeated container calls
     */
    private function getRequest()
    {
        return $this->cachedRequest ??= $this->rudra->request();
    }

    /**
     * Returns cached server object to avoid repeated method calls
     */
    private function getServer()
    {
        return $this->cachedServer ??= $this->getRequest()->server();
    }

    /**
     * Returns cached URI segments - parsed only once per request
     */
    private function getRequestUriSegments(): array
    {
        if ($this->cachedUriSegments !== null) {
            return $this->cachedUriSegments;
        }

        $uriRaw = $this->getServer()->get('REQUEST_URI');
        $parsed = parse_url($uriRaw);
        $requestPath = $parsed && isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';

        return $this->cachedUriSegments = explode('/', $requestPath);
    }

    /**
     * Spoofs the request method via _method parameter in POST requests.
     * Cached to run only once per request.
     */
    private function spoofRequestMethod(): void
    {
        if ($this->isMethodSpoofed) {
            return;
        }

        $request = $this->getRequest();
        $server = $this->getServer();
        $requestMethod = $server->get('REQUEST_METHOD');

        if ($requestMethod === 'POST' && $request->post()->has('_method')) {
            $spoofedMethod = strtoupper($request->post()->get('_method'));
            if (in_array($spoofedMethod, ['PUT', 'PATCH', 'DELETE'])) {
                $server->set(['REQUEST_METHOD' => $spoofedMethod]);
            }
        }

        $this->isMethodSpoofed = true;
    }

    /**
     * Parses request body for PUT, PATCH, DELETE methods
     */
    private function parseRequestBody(): void
    {
        $request = $this->getRequest();
        $requestMethod = $this->getServer()->get('REQUEST_METHOD');

        if (in_array($requestMethod, ['PUT', 'PATCH', 'DELETE'])) {
            $rawInput = file_get_contents('php://input');
            parse_str($rawInput, $data);
            $request->{strtolower($requestMethod)}()->set($data);
        }
    }

    /**
     * Checks if the pattern is a simple parameter (cached)
     */
    private function isSimpleParam(string $pattern): bool
    {
        $cacheKey = 'simple_' . $pattern;
        if (!isset($this->patternCache[$cacheKey])) {
            $this->patternCache[$cacheKey] = (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $pattern);
        }
        return $this->patternCache[$cacheKey];
    }

    /**
     * Matches the URI from the route with the actual request, processing parameters of the form :param and :regexp.
     * This method is used to extract dynamic segments from a URI pattern
     */
    private function handlePattern(array $route, array $request): array
    {
        $uri    = [];
        $params = null;

        foreach (explode('/', ltrim($route['url'], '/')) as $i => $segment) {
            if (!str_starts_with($segment, ':')) {
                $uri[] = $segment;
                continue;
            }

            $pattern    = substr($segment, 1);
            $hasSegment = array_key_exists($i, $request);

            // Simple parameter like :id, :page (cached check)
            if ($this->isSimpleParam($pattern)) {
                if ($hasSegment) {
                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                } else {
                    $uri[] = $segment;
                }
                continue;
            }

            // Regex parameter like :[\d]+ or :[a-z]{1,3}
            if ($hasSegment && @preg_match("/^$pattern$/", $request[$i]) === 1) {
                $uri[]    = $request[$i];
                $params[] = $request[$i];
            } else {
                $uri[] = $hasSegment ? '!@#$%^&*' : $segment;
            }
        }

        return [$uri, $params];
    }

    /**
     * Calls the controller associated with the route — either a Closure or a controller method.
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
     *
     * @throws RouterException
     */
    #[\Override]
    public function directCall(array $route, ?array $params = null): void
    {
        $controller = $this->rudra->get($route['controller']);
        $action     = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new RouterException('Service Unavailable', 503);
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
     *
     * @throws RouterException
     */
    private function callActionThroughReflection(?array $params, string $action, object $controller): void
    {
        if ($params && in_array('', $params, true)) {
            throw new RouterException('Not Found', 404);
        }

        $cacheKey = get_class($controller) . "::$action";

        if (!isset($this->reflectionCache[$cacheKey])) {
            $this->reflectionCache[$cacheKey] = [
                'method' => new \ReflectionMethod($controller, $action),
            ];
        }

        $method    = $this->reflectionCache[$cacheKey]['method'];
        $arguments = $this->rudra->getParamsIoC($method, $params);
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
     *
     * @throws RouterException
     * @throws \TypeError
     * @throws \ArgumentCountError
     */
    private function callActionThroughException(?array $params, string $action, object $controller): void
    {
        if (isset($params) && in_array('', $params, true)) {
            throw new RouterException('Not Found', 404);
        }

        try {
            if (empty($params)) {
                $controller->$action();
            } else {
                $controller->$action(...$params);
            }
        } catch (\ArgumentCountError $e) {
            $trace = $e->getTrace()[0];
            $this->rudra->autowire($this->rudra->get($trace['class']), $trace['function']);
        } catch (\TypeError $e) {
            $trace = $e->getTrace()[0];
            $this->rudra->autowire($this->rudra->new($trace['class']), $trace['function'], $trace['args']);
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
     *
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
                $params = is_array($current[1]) ? $current[1] : [$current[1]];
                $middleware($chain, ...$params);
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
            throw new MiddlewareException('Failed to process middleware: ' . json_encode($current), 0, $e);
        }
    }
}
