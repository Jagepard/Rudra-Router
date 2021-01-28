<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router\Traits;

use Rudra\Exceptions\RouterException;

trait RouterMatchTrait
{
    protected function handleHttpMethod(array $route): void
    {
        if (strpos($route["http_method"], '|') !== false) {
            $httpMethods = explode('|', $route["http_method"]);

            foreach ($httpMethods as $httpMethod) {
                $route["http_method"] = $httpMethod;
                $this->matchRequest($route);
            }
        }

        $this->matchRequest($route);
    }

    protected function matchRequest(array $route): void
    {
        if ($route["http_method"] == $this->rudra()->request()->server()->get("REQUEST_METHOD")) {
            $request = parse_url(trim($this->rudra()->request()->server()->get("REQUEST_URI"), '/'))["path"];

            list($uri, $params) = $this->handlePattern($route, explode('/', $request));
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
            if (preg_match('/{([a-zA-Z0-9]*?)}/', $pattern[$i]) !== 0) {
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

    protected function setCallable(array $route, $params)
    {
        if ($route["method"] instanceof \Closure) {
            (is_array($params)) ? $route["method"](...$params) : $route["method"]($params);
            return;
        }

        $route["controller"] = $this->setClassName($route["controller"], $this->namespace . "Controllers\\");
        $this->directCall($route, $params);
    }

    protected function setClassName(string $className, string $namespace): string
    {
        $className = (strpos($className, ":fq") !== false)
            ? explode(':', $className)[0]
            : $namespace . $className;

        if (!class_exists($className)) {
            throw new RouterException("503");
        }

        return $className;
    }
}
