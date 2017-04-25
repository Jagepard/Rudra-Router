<?php
/**
 * Date: 25.04.17
 * Time: 12:37
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


/**
 * Class MatchRequest
 *
 * @package Rudra
 */
class MatchRequest
{

    use SetContainerTrait;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var bool
     */
    protected $token = false;

    /**
     * MatchRequest constructor.
     *
     * @param ContainerInterface $container
     * @param Router             $router
     */
    public function __construct(ContainerInterface $container, Router $router)
    {
        $this->container = $container;
        $this->router    = $router;
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }

    /**
     * @param array $route
     *
     * @return bool|void
     */
    public function matchRequest(array $route)
    {
        if ($route['http_method'] == $this->container()->getServer('REQUEST_METHOD')) {

            $requestUrl   = trim($this->container()->getServer('REQUEST_URI'), '/');
            $requestArray = explode('/', $requestUrl);
            list($params, $completeRequestArray) = $this->handlePattern($route, $requestArray);
            list($requestString, $realRequestString) = $this->handleCompleteRequestArray($completeRequestArray);
            $outRequestUrl = $this->getOutRequestUrl($requestUrl);

            // Это нужно для обработки 404 ошибки
            if (isset($requestString)) {
                // Проверяем строку запроса на соответсвие маршруту
                preg_match("/$requestString/", $requestUrl, $matches);

                // Если совпадений нет, то возвращаем $this->isToken() == false
                if (!isset($matches[0])) {
                    return $this->router()->isToken();
                }
            }

            return $this->handleRequest($route, $realRequestString, $outRequestUrl, $params);
        }
    }

    /**
     * @param array $route
     * @param array $requestArray
     *
     * @return array
     */
    protected function handlePattern(array $route, array $requestArray): array
    {
        $i                    = 0;
        $params               = null;
        $paramsKey            = [];
        $matchesPattern       = [];
        $completeRequestArray = [];

        // Обходим элементы массива $pattern
        foreach (explode('/', ltrim($route['pattern'], '/')) as $itemPattern) {
            // Ищем совпадение строки запроса с шаблоном {...}
            if (preg_match('/{[a-zA-Z0-9]+}/', $itemPattern, $matchesPattern) != 0) {

                // Если есть элемент массива $i
                if (isset($requestArray[$i])) {
                    // Убираем {} из названия будующего ключа массива параметров
                    preg_match('/[a-zA-Z0-9]+/', $matchesPattern[0], $paramsKey);
                    // Присваиваем найденому параметру соответсвующий uri
                    $params[$paramsKey[0]]  = $requestArray[$i];
                    $completeRequestArray[] = $requestArray[$i];
                }
                // Если совпадений нет, то записываем данные не совпадающие
                // с шаблоном в отдельный массив
            } else {
                $completeRequestArray[] = $itemPattern;
            }

            $i++;
        }

        return [$params, $completeRequestArray];
    }

    /**
     * @param $completeRequestArray
     *
     * @return array
     */
    protected function handleCompleteRequestArray($completeRequestArray)
    {
        $requestString     = '';
        $realRequestString = '';

        if (count($completeRequestArray)) {
            $requestString     = implode('\/', $completeRequestArray);
            $realRequestString = implode('/', $completeRequestArray);
        }

        return [$requestString, $realRequestString];
    }

    /**
     * @param $requestUrl
     *
     * @return mixed
     */
    protected function getOutRequestUrl($requestUrl)
    {
        $outRequestUrl = [];

        if (strpos($requestUrl, '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $requestUrl, $outRequestUrl);

            return $outRequestUrl;
        }

        $outRequestUrl[0] = $requestUrl;

        return $outRequestUrl;
    }

    /**
     * @param array $route
     * @param       $realRequestString
     * @param       $outRequestUrl
     * @param       $params
     *
     * @return bool
     */
    protected function handleRequest(array $route, $realRequestString, $outRequestUrl, $params)
    {
        // Если $realRequestString совпадает с 'REQUEST_URI'
        if ($realRequestString == $outRequestUrl[0]) {
            // Устанавливаем token true
            $this->setToken(true);
            $this->router()->setCallable($route, $params);

            if (DEV !== 'test') {
                return false; // @codeCoverageIgnore
            }
        }
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->token;
    }
}
