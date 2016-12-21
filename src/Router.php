<?php

/**
 * Date: 05.09.16
 * Time: 14:51
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */
declare(strict_types = 1);

namespace Rudra;

/**
 * Class Router
 *
 * @package Rudra
 */
final class Router
{

    /**
     * @var bool
     */
    private $token = false;

    /**
     * @var Controller
     */
    private $new;

    /**
     * @var IContainer
     */
    private $di;

    /**
     * @var
     */
    private $namespace;

    /**
     * Router constructor.
     *
     * @param \Rudra\IContainer $di
     * @param                   $namespace
     */
    public function __construct(IContainer $di, $namespace)
    {
        $this->di        = $di;
        $this->namespace = $namespace;
    }

    /**
     * @param string $pattern
     * @param        $classAndMethod
     *
     * @return bool
     */
    public function set(string $pattern, $classAndMethod)
    {
        // Исходные данные для инкремента
        $i = 0;

        // Строка запроса
        $requestUrl = trim($this->getDi()->getServer('REQUEST_URI'), '/');
        // Разбираем данные $_SERVER['REQUEST_URI'] по '/'
        $requestArray = explode('/', $requestUrl);

        // Обходим элементы массива $pattern
        foreach (explode('/', $pattern) as $itemPattern) {

            if (strpos($itemPattern, '::') !== false) {
                $patternData   = explode('::', $itemPattern);
                $requestMethod = ($patternData[1] !== null) ? $patternData[1] : 'GET';
                $itemPattern   = $patternData[0];

                if (array_key_exists(2, $patternData)) {
                    $hasMethod = 'has' . ucfirst(strtolower($patternData[1]));
                    $getMethod = 'get' . ucfirst(strtolower($patternData[1]));
                    $key       = $patternData[2];

                    if ($this->getDi()->$hasMethod($key)) {
                        $params[$key] = $this->getDi()->$getMethod($key);
                    }
                }

            } else {
                $requestMethod = 'GET';
            }

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

            // Инкремент
            $i++;
        }

        if (isset($completeRequestArray)) {
            $requestString     = implode('\/', $completeRequestArray);
            $realRequestString = implode('/', $completeRequestArray);
        }

        if (strpos($requestUrl, '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $requestUrl, $OutRequestUrl);
        } else {
            $OutRequestUrl[0] = $requestUrl;
        }

        // Это нужно для обработки 404 ошибки
        if (isset($requestString)) {
            // Проверяем строку запроса на соответсвие маршруту
            preg_match("/$requestString/", $requestUrl, $matches);

            // Если совпадений нет, то возвращаем $this->isToken() == false
            if (!isset($matches[0])) {
                return $this->isToken();
            }
        }

        // Если запрашиваем метод совпадаеи с $_SERVER['REQUEST_METHOD']
        // и $realRequestString совпадает с $_SERVER['REQUEST_URI']
        if ($requestMethod == $this->getDi()->getServer('REQUEST_METHOD') && $realRequestString == $OutRequestUrl[0]) {
            // Устанавливаем token true
            $this->setToken(true);

            // Если $classAndMethod является экземпляром ксласса Closure
            // возвращаем замыкание
            if ($classAndMethod instanceof \Closure) {
                return $classAndMethod();
            }

            // Создаем экземпляр класса
            $this->setNew(new $classAndMethod[0]());
            // Инициализуруем
            $this->getNew()->init($this->getDi());
            // Выполняем метод before до основного вызова
            $this->getNew()->before();
            // Собственно вызываем экшн, в зависимости от наличия параметров

            isset($params) ? $this->getNew()->{$classAndMethod[1]}($params) : $this->getNew()->{$classAndMethod[1]}();
            // Выполняем метод after
            $this->getNew()->after();
            exit;
        }
    }

    /**
     * @param     $class
     * @param     $method
     * @param int $number
     */
    public function annotation($class, $method, $number = 0)
    {
        if (strpos($class, '::namespace') !== false) {
            $classParams = explode('::', $class);
            $class       = $classParams[0];
        } else {
            $class = $this->getNamespace() . $class;
        }

        if (strpos($method, '::') !== false) {
            $arrayParams = explode('::', $method);
            $method      = $arrayParams[0];
        }

        $result = $this->getDi()->get('annotation')->getMethodAnnotations($class, $method);

        if (isset($result['Routing'])) {
            $this->set($result['Routing'][$number]['url'], [$class, $method]);
        }
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token)
    {
        $this->token = $token;
    }

    /**
     * @return Controller
     */
    public function getNew(): Controller
    {
        return $this->new;
    }

    /**
     * @param Controller $new
     */
    public function setNew(Controller $new)
    {
        $this->new = $new;
    }

    /**
     * @return IContainer
     */
    public function getDi(): IContainer
    {
        return $this->di;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    public function error404()
    {
        $this->getDi()->get('redirect')->responseCode('404');
        echo 'Нет такой страницы: <h1>«Ошибка 404»</h1>';
    }

}
