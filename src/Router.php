<?php declare(strict_types = 1);

namespace Rudra;

/**
 * Date: 14.07.15
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
     * @param string $request
     * @param        $classAndMethod
     * @param string $requestMethod
     * @return bool
     */
    public function set(string $request, $classAndMethod, string $requestMethod = 'GET')
    {
        // Исходные данные для инкремента
        $i = 0;

        // Строка запроса
        $InRequestUrl = trim($_SERVER['REQUEST_URI'], '/');

        // Разбираем заврос в массив по '/'
        $requestArray = explode('/', $request);

        // Обходим элементы массива
        foreach ($requestArray as $url) {
            // Инкремент
            $i++;
            // Ищем совпадение строки запроса с шаблоном {?...}
            if (preg_match('/{\?[a-zA-Z0-9]+}/', $url, $matches) != 0) {
                // Разбираем данные $_SERVER['REQUEST_URI'] по '/'
                $serverRequest = explode('/', $InRequestUrl);
                // Если есть элемент массива $i - 1
                if (isset($serverRequest[$i - 1])) {
                    // Присваиваем найденому параметру соответсвующий uri
                    $params[$matches[0]] = $serverRequest[$i - 1];
                }
                // Если совпадений нет, то записываем данные не совпадающие
                // с шаблоном в отдельный массив
            } else {
                $newRequestArray[] = $url;
            }
        }

        // Если массив параметров не пуст
        if (isset($params)) {
            // Строка параметров типа param1\/param2\/paramN
            $paramsString = implode('\/', $params);
            // Строка параметров типа param1/param2/paramN
            $realParamsString = implode('/', $params);

            // Если в запросе есть данные не являющиеся параметрами
            if (isset($newRequestArray)) {
                // Создаем строки из данных не являющихся параметрами
                // добавляем уже созданные строки параметров
                $requestString     = implode('\/', $newRequestArray) . '\/' . $paramsString;
                $realRequestString = implode('/', $newRequestArray) . '/' . $realParamsString;
            } else {
                // Это на случай ели других данных помимо параметров нет
                $requestString     = $paramsString;
                $realRequestString = $realParamsString;
            }

            // Это в случае если параметров нет
        } else {
            $requestString     = implode('\/', $newRequestArray);
            $realRequestString = implode('/', $newRequestArray);
        }

        // Это нужно для обработки 404 ошибки
        if (isset($requestString)) {
            // Проверяем строку запроса на соответсвие маршруту
            preg_match("/$requestString/", $InRequestUrl, $matches);

            // Если совпадений нет, то возвращаем $this->isToken() == false
            if (!isset($matches[0])) {
                return $this->isToken();
            }
        }

        if (strpos($InRequestUrl, '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $InRequestUrl, $OutRequestUrl);
        } else {
            $OutRequestUrl[0] = $InRequestUrl;
        }

        // Если запрашиваем метод совпадаеи с $_SERVER['REQUEST_METHOD']
        // и $realRequestString совпадает с $_SERVER['REQUEST_URI']
        if ($requestMethod == $_SERVER['REQUEST_METHOD'] && $realRequestString == $OutRequestUrl[0]) {
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
     * @return iApplication
     */
    public function getDi(): iApplication
    {
        return $this->di;
    }

    /**
     * @param Controller $obj
     */
    public function error404(Controller $obj)
    {
        return $obj->errorPage();
    }
}