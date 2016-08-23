<?php
/**
 * Date: 23.08.16
 * Time: 14:51
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


final class Autorouter
{
    // Контроллер по умолчанию
    /**
     * @var
     * Контроллер по умолчанию
     * при создании объекта конструктор присваивает
     * свойству значение Config::$defaultController
     */
    private $defaultController;

    /**
     * @var
     * Массив для хранения объекта контроллера,
     * данные присваиваются в методе newObject()
     */
    private $newObject;

    /**
     * @var
     * Строка запроса, значение присваивается в методе
     * returnRequest(), который разберает данные url
     */
    private $request;

    /**
     * @var
     * Имя класса, присваивается в методе className(),
     * который вызывается в зависимости от метода setName()
     */
    private $className;

    /**
     * @var
     * Имя метода, присваивается в методе methodName(),
     * который вызывается в зависимости от метода setName()
     */
    private $methodName;

    /**
     * @var
     * Массив параметров, присваивается в методе fetchParams(),
     * который вызывается в зависимости от метода setName()
     */
    private $params;

    /**
     * @var iContainer
     */
    private $di;

    /**
     * Router constructor.
     * @param iContainer $di
     */
    public function __construct(iContainer $di)
    {
        $this->di = $di;
    }

    /**
     * @param              $config
     * Соединяется с БД
     * Присваевает контроллер по умолчанию
     * Запускает приложение
     */
    public function run($config)
    {
        $this->defaultController = $config::$defaultController;
        $this->request($config);
    }

    /**
     * @param $config
     * Создает объект и вызывает метод объекта
     * в зависимости от параметров request
     */
    private function request($config)
    {
        // Обрабатывает данные адресной строки
        $this->returnRequest($config);
        /*
         * Присваевает значение $this->request
         * в зависимости от наличия get запроса
         */
        if (strpos($this->request, '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $this->request, $matches);
        } else {
            $matches[0] = $this->request;
        }
        // Результат присваиваем $this->request
        $this->request = $matches[0];

        /*
         * Устанавливает имя метода и контроллера
         * которые необходимо вызвать
         */
        $this->setName();
        // Вызывает необходимый метод контроллера
        $this->newObject($this->params);
        exit;
    }

    /**
     * @param $config
     * Присваивает $this->request данные
     * $_SERVER['REQUEST_URI'] или $_GET['r']
     * в зависимости от параметра Config::URI
     */
    private function returnRequest($config)
    {
        switch ($config::URI) {
            case 'REQUEST':
                $this->request = trim($_SERVER['REQUEST_URI'], '/');
                break;
            case 'GET':
                $this->request = trim($_GET['r'], '/');
                break;
        }
    }

    /**
     * Устанавливает имя метода и контроллера
     * которые необходимо вызвать в зависимости
     * от параметров вводимых в адресной строке
     */
    private function setName()
    {
        /*
         * Если в строке запроса есть хотя бы 1 '/'
         * Например: http://domain.com/value/value
         */
        if (strpos($this->request, '/') !== false) {
            $this->request = explode('/', $this->request);

            /*
             * Убираем пустые элементы массива, которые могли образоваться
             * если в адресной строке были указаны лишние '/'
             */
            foreach ($this->request as $item) {
                if (!$item == '') {
                    $request[] = $item;
                }
            }
            $this->request = $request;

            //Action/params
            if (method_exists($this->defaultController[1], $this->methodName($this->request[0]))) {
                $this->className = $this->defaultController[1];
                $this->methodName = $this->methodName($this->request[0]);
                $this->fetchParams(1);

            } elseif (class_exists(ucfirst($this->request[0]) . '\\' . $this->className($this->request[1]))) {
                $this->className = ucfirst($this->request[0]) . '\\' . $this->className($this->request[1]);

                //Module/Controller/Action/params
                if (isset($this->request[2])) {
                    if (method_exists($this->className, $this->methodName($this->request[2]))) {
                        $this->methodName = $this->methodName($this->request[2]);
                        $this->fetchParams(3);

                        //Module/Controller[DefaultAction]/params
                    } else {
                        $this->methodName = $this->methodName($this->defaultController[3]);
                        $this->fetchParams(2);
                    }
                } else {
                    if (method_exists($this->className, $this->methodName(null))) {
                        $this->methodName = $this->methodName(null);
                        $this->fetchParams(3);

                        //Module/Controller[DefaultAction]/params
                    } else {
                        $this->methodName = $this->methodName($this->defaultController[3]);
                        $this->fetchParams(2);
                    }
                }

            } elseif (class_exists(ucfirst($this->defaultController[0]) . '\\' . $this->className($this->request[0]))) {
                $this->className = ucfirst($this->defaultController[0]) . '\\' . $this->className($this->request[0]);

                //[DefaultModule]Controller/Action/params
                if (method_exists($this->className, $this->methodName($this->request[1]))) {
                    $this->methodName = $this->methodName($this->request[1]);
                    $this->fetchParams(2);

                    //[DefaultModule]Controller[Action]/params
                } else {
                    $this->methodName = $this->methodName($this->defaultController[3]);
                    $this->fetchParams(1);
                }
                //[DefaultModule][DefaultController]Action/params
            } else {
                $this->fetchParams(0);
                $this->className = $this->defaultController[1];
                $this->methodName = $this->methodName($this->defaultController[2]);
            }

        } else {
            //[DefaultModule][DefaultController][DefaultAction]
            if (empty($this->request)) {
                $this->className = $this->defaultController[1];
                $this->methodName = $this->methodName($this->defaultController[2]);

                //Если есть значение http://root/[value]
            } elseif (isset($this->request)) {
                //[DefaultModule][DefaultController]Action
                if (method_exists($this->defaultController[1], $this->methodName($this->request))) {
                    $this->className = $this->defaultController[1];
                    $this->methodName = $this->methodName($this->request);

                    //[DefaultModule])Controller[DefaultAction]
                } elseif (class_exists(ucfirst($this->defaultController[0]) . '\\' . $this->className($this->request))
                    and method_exists(
                        ucfirst($this->defaultController[0]) . '\\' . $this->className($this->request),
                        $this->methodName($this->defaultController[3])
                    )
                ) {
                    $this->className = ucfirst($this->defaultController[0]) . '\\' . $this->className($this->request);
                    $this->methodName = $this->methodName($this->defaultController[3]);

                } else {
                    //params
                    $this->params[] = $this->request;
                    $this->className = $this->defaultController[1];
                    $this->methodName = $this->methodName($this->defaultController[2]);
                }
            }
        }
    }

    /**
     * @param $value
     * @return string
     * Возвращает имя метода вида actionValue
     */
    private function methodName($value)
    {
        return 'action' . ucfirst($value);
    }

    /**
     * @param $value
     * Разбирает $this->request
     */
    private function fetchParams($value)
    {
        if (isset($this->request[$value])) {
            if (count($this->request) > $value) {
                for ($i = $value; $i < count($this->request); $i++) {
                    $this->params[] = $this->request[$i];
                }
            }
        }
    }

    /**
     * @param $value
     * @return string
     * Возвращает имя контроллера вида ValueController
     */
    private function className($value)
    {
        return ucfirst($value) . 'Controller';
    }

    /**
     * @param bool $params
     * Создает объект и дергает метод контроллера
     */
    private function newObject($params = false)
    {
        if (class_exists($this->className)) {
            if (method_exists($this->className, $this->methodName)) {
                $this->newObject[$this->className] = new $this->className();
                $this->newObject[$this->className]->init($this->di);
                $this->newObject[$this->className]->before();
                $this->newObject[$this->className]->{$this->methodName}($params);
                $this->newObject[$this->className]->after();
            }
        }
    }
}