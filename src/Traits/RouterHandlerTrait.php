<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Exceptions\RouterException;

trait RouterHandlerTrait
{
    public function handleRequestMethod(array $route): void
    {
        $requestMethod = $this->rudra()->request()->server()->get("REQUEST_METHOD");

        if ($this->rudra()->request()->post()->has("_method") && $requestMethod === "POST")
            $this->rudra()->request()->server()->set(["REQUEST_METHOD" => $this->rudra()->request()->post()->get("_method")]);

        if (in_array($requestMethod, ["PUT", "PATCH", "DELETE"])) {
            parse_str(file_get_contents("php://input"), $data);
            $this->rudra()->request()->{strtolower($requestMethod)}()->set($data);
        }

        $this->handleHttpMethod($route);
    } // @codeCoverageIgnore

    protected function handleHttpMethod(array $route): void
    {
        if (strpos($route["http_method"], '|') !== false) {
            $httpMethods = explode('|', $route["http_method"]);

            foreach ($httpMethods as $httpMethod) {
                $route["http_method"] = $httpMethod;
                $this->handleRequestUri($route);
            }
        }

        $this->handleRequestUri($route);
    }

    protected function handleRequestUri(array $route): void
    {
        if ($route["http_method"] == $this->rudra()->request()->server()->get("REQUEST_METHOD")) {
            $request = parse_url(ltrim($this->rudra()->request()->server()->get("REQUEST_URI"), '/'))["path"];
            [$uri, $params] = $this->handlePattern($route, explode('/', $request));
            (implode('/', $uri) !== $request) ?: $this->setCallable($route, $params);
        }
    }

    protected function handlePattern(array $route, array $request): array
    {
        $uri     = [];
        $params  = null;
        $pattern = explode('/', ltrim($route["pattern"], '/'));
        $count   = count($pattern);

        for ($i = 0; $i < $count; $i++) {
            // Looking for a match with a pattern {...}
            if (preg_match('/{([a-zA-Z0-9_]*?)}/', $pattern[$i]) !== 0) {
                if (array_key_exists($i, $request)) {
                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                }
                continue;
            }

            $uri[] = $pattern[$i];
        }

        return [$uri, $params];
    }

    public function handleMiddleware(array $middleware, bool $fullName = false)
    {
        foreach ($middleware as $current) {
            $middlewareName = (!$fullName) ? $this->setClassName($current[0], $this->namespace . "Middleware\\") : $current[0];
            (isset($current[1])) ? (new $middlewareName())($current[1]) : (new $middlewareName())();
        }
    }
}
