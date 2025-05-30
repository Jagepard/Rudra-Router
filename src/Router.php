<?php

declare(strict_types=1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use ReflectionException;
use Rudra\Exceptions\RouterException;
use Rudra\Container\Traits\SetRudraContainersTrait;
use Rudra\Router\Traits\{RouterAnnotationTrait, RouterRequestMethodTrait};

class Router implements RouterInterface
{
    use RouterAnnotationTrait;
    use SetRudraContainersTrait;
    use RouterRequestMethodTrait;

    private array $reflectionCache = [];

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

    private function handleRequestUri(array $route): void
    {
        $this->handleRequestMethod();

        $request = $this->rudra->request();
        $server  = $request->server();

        if ($route['method'] !== $server->get('REQUEST_METHOD')) {
            return;
        }

        $uriRaw         = $server->get('REQUEST_URI');
        $parsed         = parse_url($uriRaw);
        $requestPath    = $parsed && isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';
        $uriSegments    = explode('/', $requestPath);
        [$uri, $params] = $this->handlePattern($route, $uriSegments);


        if ($uri === $uriSegments) {
            $this->setCallable($route, $params);
        }
    }

    private function handleRequestMethod(): void
    {
        $request       = $this->rudra->request();
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

    private function handlePattern(array $route, array $request): array
    {
        $uri     = [];
        $params  = null;
        $subject = explode('/', ltrim($route['url'], '/'));
        $count   = count($subject);

        for ($i = 0; $i < $count; $i++) {
            if (preg_match("/^:[a-zA-Z0-9_-]+$/", $subject[$i]) > 0 && array_key_exists($i, $request)) {
                $value    = $request[$i];
                $uri[]    = $value;
                $params[] = $value;
                continue;
            }
            
            if (preg_match("/^:([\\[\\]\\\\:a-zA-Z0-9_\\-{,}]+)$/", $subject[$i], $matches)) {
                if (array_key_exists($i, $request)) {
                    $pattern = $matches[1];
                    if (preg_match("/^$pattern$/", $request[$i])) {
                        $uri[]    = $request[$i];
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

    private function setCallable(array $route, $params): void
    {
        if ($route['controller'] instanceof \Closure) {
            if (is_array($params)) {
                $route['controller'](...$params);
            } else {
                $route['controller']($params);
            }

            exit();
        }

        $this->directCall($route, $params);
    }

    public function directCall(array $route, $params = null): void
    {
        $controller = $this->rudra->get($route['controller']);
        $action     = $route['action'];

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

    private function callActionThroughException($params, $action, $controller): void
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

    public function handleMiddleware(array $chainOfMiddlewares): void
    {
        if (!$chainOfMiddlewares) {
            return;
        }

        $current = array_shift($chainOfMiddlewares);

        if (is_array($current) && count($current) === 2 && is_string($current[0])) {
            $middleware = new $current[0]();
            $middleware($current[1], $chainOfMiddlewares);
            return;
        }

        if (is_array($current) && is_string($current[0])) {
            $middleware = new $current[0]();
            $middleware($chainOfMiddlewares);
            return;
        }

        if (is_string($current)) {
            $middleware = new $current();
            $middleware($chainOfMiddlewares);
            return;
        }

        throw new \InvalidArgumentException('Invalid middleware format');
    }
}
