<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Router;

use Rudra\Exceptions\RouterException;
use Rudra\Router\Traits\RouterAnnotationTrait;
use Rudra\Router\Traits\RouterRequestMethodTrait;
use Rudra\Container\Traits\SetRudraContainersTrait;

class Router implements RouterInterface
{
    use SetRudraContainersTrait;
    use RouterRequestMethodTrait;
    use RouterAnnotationTrait;

    /**
     * @param array $route
     * @throws RouterException
     */
    public function set(array $route): void
    {
        if (strpos($route['method'], '|') !== false) {
            $httpMethods = explode('|', $route['method']);

            foreach ($httpMethods as $httpMethod) {
                $route['method'] = $httpMethod;
                $this->handleRequestUri($route);
            }
        }

        $this->handleRequestUri($route);
    }

    /**
     * @param array $route
     * @param null $params
     * @throws RouterException
     */
    public function directCall(array $route, $params = null): void
    {
        $controller = new $route['controller']();
        $action     = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new RouterException("503");
        }

        $controller->shipInit();
        $controller->containerInit();
        $controller->init();
        $controller->before();
        !isset($route['middleware']["before"]) ?: $this->handleMiddleware($route['middleware']["before"]);
        $this->callAction($params, $action, $controller);
        !isset($route['middleware']["after"]) ?: $this->handleMiddleware($route['middleware']["after"]);
        $controller->after();

        if ($this->rudra->config()->get("environment") !== "test") {
            exit();
        }
    }

    protected function handleRequestMethod(): void
    {
        $requestMethod = $this->rudra->request()->server()->get("REQUEST_METHOD");

        if ($requestMethod === "POST" && $this->rudra->request()->post()->has("_method")) {
            $this->rudra->request()->server()->set(["REQUEST_METHOD" => $this->rudra->request()->post()->get("_method")]);
        }

        if (in_array($requestMethod, ["PUT", "PATCH", "DELETE"])) {
            parse_str(file_get_contents("php://input"), $data);
            $this->rudra->request()->{strtolower($requestMethod)}()->set($data);
        }
    }

    /**
     * @param array $route
     * @throws RouterException
     */
    protected function handleRequestUri(array $route)
    {
        $this->handleRequestMethod();

        if ($route['method'] == $this->rudra->request()->server()->get("REQUEST_METHOD")) {
            $requestString  = parse_url(ltrim($this->rudra->request()->server()->get("REQUEST_URI"), '/'))["path"] ?? "";
            [$uri, $params] = $this->handlePattern($route, explode('/', $requestString));

            if (implode('/', $uri) === $requestString) {
                $this->setCallable($route, $params);
            }
        }
    }

    /**
     * @param array $route
     * @param       $params
     * @throws RouterException
     */
    protected function setCallable(array $route, $params)
    {
        if ($route['controller'] instanceof \Closure) {
            (is_array($params)) ? $route['controller'](...$params) : $route['controller']($params);
            return;
        }

        $this->directCall($route, $params);
    }

    /**
     * @param $params
     * @param $action
     * @param $controller
     * @throws RouterException
     */
    protected function callAction($params, $action, $controller): void
    {
        if (!isset($params)) {
            $controller->{$action}();
        } else {
            if (!in_array("", $params)) {
                $controller->{$action}(...$params);
            } else {
                throw new RouterException("404");
            }
        }
    }

    /**
     * @param array $route
     * @param array $request
     * @return array
     */
    protected function handlePattern(array $route, array $request): array
    {
        $uri     = [];
        $params  = null;
        $subject = explode('/', ltrim($route['url'], '/'));
        $count   = count($subject);

        for ($i = 0; $i < $count; $i++) {
            // Looking for a match with a subject :...
            if (preg_match("/^:[a-zA-Z0-9_-]+$/", $subject[$i]) !== 0) {
                if (array_key_exists($i, $request)) {                  
                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                }

                continue;
            } elseif (preg_match("/^:[\[\]\\\:a-zA-Z0-9_-{,}]+$/", $subject[$i])) {
                if (array_key_exists($i, $request)) {
                    $pattern = substr($subject[$i], 1);

                    if (!preg_match("/^$pattern+$/", $request[$i])) {
                        continue;
                    }

                    $uri[]    = $request[$i];
                    $params[] = $request[$i];
                }

                continue;
            }

            $uri[] = $subject[$i];
        }

        return [$uri, $params];
    }

    /**
     * @param array $chainOfMiddlewares
     */
    public function handleMiddleware(array $chainOfMiddlewares)
    {
        $current = array_shift($chainOfMiddlewares);

        if ((is_array($current)) && count($current) === 2) {
            (new $current[0]())($current[1], $chainOfMiddlewares);
            return;
        }

        (is_array($current)) ? (new $current[0]())($chainOfMiddlewares) : (new $current())($chainOfMiddlewares);
    }
}
