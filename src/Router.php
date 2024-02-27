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
        if (strpos($route[1], '|') !== false) {
            $httpMethods = explode('|', $route[1]);

            foreach ($httpMethods as $httpMethod) {
                $route[1] = $httpMethod;
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
        if ((count($route) < 3) or (count($route[2]) !== 2)) {
            throw new RouterException("503");
        }

        $controller = new $route[2][0]($this->rudra());
        $action     = $route[2][1];

        if (!method_exists($controller, $action)) {
            throw new RouterException("503");
        }

        $controller->shipInit();
        $controller->containerInit();
        $controller->init();
        $controller->before();
        !isset($route[3]["before"]) ?: $this->handleMiddleware($route[3]["before"]);
        $this->callAction($params, $action, $controller);
        !isset($route[3]["after"]) ?: $this->handleMiddleware($route[3]["after"]);
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

        if ($route[1] == $this->rudra->request()->server()->get("REQUEST_METHOD")) {
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
        if ($route[2] instanceof \Closure) {
            (is_array($params)) ? $route[2](...$params) : $route[2]($params);
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
        $pattern = explode('/', ltrim($route[0], '/'));
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
