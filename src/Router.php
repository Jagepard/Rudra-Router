<?php declare(strict_types = 1);

namespace Rudra;

    /**
     * Date: 05.09.16
     * Time: 14:51
     * @author    : Korotkov Danila <dankorot@gmail.com>
     * @copyright Copyright (c) 2014, Korotkov Danila
     * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
     */

/**
 * Class Router
 * @package Core
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
     * @var iContainer
     */
    private $di;

    /**
     * Route constructor.
     * @param iContainer $di
     */
    public function __construct(iContainer $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $pattern
     * @param        $classAndMethod
     * @param string $requestMethod
     * @return bool
     */
    public function set(string $pattern, $classAndMethod, string $requestMethod = 'GET')
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
                $patternData      = explode('::', $itemPattern);
                $hasMethod = 'has' . ucfirst($patternData[1]);
                $getMethod = 'get' . ucfirst($patternData[1]);
                $key       = $patternData[2];

                if ($this->getDi()->$hasMethod($key)) {
                    $itemPattern  = $patternData[0];
                    $params[$key] = $this->getDi()->$getMethod($key);
                }
            }

            // Ищем совпадение строки запроса с шаблоном {...}
            if (preg_match('/{[a-zA-Z0-9]+}/', $itemPattern, $matchesPattern) != 0) {
                // Если есть элемент массива $i
                if (isset($requestArray[$i])) {
                    // Убираем {} из названия будующего ключа массива параметров
                    preg_match('/[a-zA-Z0-9]+/', $matchesPattern[0], $paramsKey);
                    // Присваиваем найденому параметру соответсвующий uri
                    $params[$paramsKey[0]] = $requestArray[$i];
                }
                $completeRequestArray[] = $requestArray[$i];
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
     * @return iContainer
     */
    public function getDi(): iContainer
    {
        return $this->di;
    }

    public function error404()
    {
        $this->getDi()->get('redirect')->responseCode('404');
        echo 'Нет такой страницы: <h1>«Ошибка 404»</h1>';
    }
}