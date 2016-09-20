<?php

/**
 * Date: 23.08.16
 * Time: 14:51
 * 
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2014, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class Autorouter
 * @package Rudra
 */
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
     * @var IContainer
     */
    private $di;

    /**
     * Router constructor.
     * @param IContainer $di
     */
    public function __construct(IContainer $di)
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
        $this->setDefaultController($config::$defaultController);
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
        if (strpos($this->getRequest(), '?') !== false) {
            preg_match('~[/[:word:]-]+(?=\?)~', $this->getRequest(), $matches);
        } else {
            $matches[0] = $this->getRequest();
        }
        // Результат присваиваем $this->request
        $this->setRequest($matches[0]);

        /*
         * Устанавливает имя метода и контроллера
         * которые необходимо вызвать
         */
        $this->setName();
        // Вызывает необходимый метод контроллера
        $this->newObject($this->getParams());
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
        if ('REQUEST' == $config::URI) {
            $this->setRequest(trim($this->getDi()->getServer('REQUEST_URI'), '/'));
        } elseif ('GET' == $config::URI) {
            $this->setRequest(trim($this->getDi()->getGet('r'), '/'));
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
        if (strpos($this->getRequest(), '/') !== false) {
            $this->setRequest(explode('/', $this->getRequest()));

            /*
             * Убираем пустые элементы массива, которые могли образоваться
             * если в адресной строке были указаны лишние '/'
             */
            foreach ($this->getRequest() as $item) {
                if (!$item == '') {
                    $request[] = $item;
                }
            }
            $this->setRequest($request);

            //Action/params
            if (method_exists($this->getDefaultController(1), $this->methodName($this->getRequestKey(0)))) {
                $this->setClassName($this->getDefaultController(1));
                $this->setMethodName($this->methodName($this->getRequestKey(0)));
                $this->fetchParams(1);
            } elseif (class_exists(ucfirst($this->getRequestKey(0)) . '\\' . $this->className($this->getRequestKey(1)))) {
                $this->setClassName(ucfirst($this->getRequestKey(0)) . '\\' . $this->className($this->getRequestKey(1)));

                //Module/Controller/Action/params
                if (null !== $this->getRequestKey(2)) {
                    if (method_exists($this->getClassName(), $this->methodName($this->getRequestKey(2)))) {
                        $this->setMethodName($this->methodName($this->getRequestKey(2)));
                        $this->fetchParams(3);

                        //Module/Controller[DefaultAction]/params
                    } else {
                        $this->setMethodName($this->methodName($this->getDefaultController(3)));
                        $this->fetchParams(2);
                    }
                } else {
                    if (method_exists($this->getClassName(), $this->methodName(null))) {
                        $this->setMethodName($this->methodName(null));
                        $this->fetchParams(3);

                        //Module/Controller[DefaultAction]/params
                    } else {
                        $this->setMethodName($this->methodName($this->getDefaultController(3)));
                        $this->fetchParams(2);
                    }
                }
            } elseif (class_exists(ucfirst($this->getDefaultController(0)) . '\\' . $this->className($this->getRequestKey(0)))) {
                $this->setClassName(ucfirst($this->getDefaultController(0)) . '\\' . $this->className($this->getRequestKey(0)));

                //[DefaultModule]Controller/Action/params
                if (method_exists($this->getClassName(), $this->methodName($this->getRequestKey(1)))) {
                    $this->setMethodName($this->methodName($this->getRequestKey(1)));
                    $this->fetchParams(2);

                    //[DefaultModule]Controller[Action]/params
                } else {
                    $this->setMethodName($this->methodName($this->getDefaultController(3)));
                    $this->fetchParams(1);
                }
                //[DefaultModule][DefaultController]Action/params
            } else {
                $this->fetchParams(0);
                $this->setClassName($this->getDefaultController(1));
                $this->setMethodName($this->methodName($this->getDefaultController(2)));
            }
        } else {
            //[DefaultModule][DefaultController][DefaultAction]
            if (empty($this->getRequest())) {
                $this->setClassName($this->getDefaultController(1));
                $this->setMethodName($this->methodName($this->getDefaultController(2)));

                //Если есть значение http://root/[value]
            } elseif (null !== $this->getRequest()) {
                //[DefaultModule][DefaultController]Action
                if (method_exists($this->getDefaultController(1), $this->methodName($this->getRequest()))) {
                    $this->setClassName($this->getDefaultController(1));
                    $this->setMethodName($this->methodName($this->getRequest()));

                    //[DefaultModule])Controller[DefaultAction]
                } elseif (class_exists(ucfirst($this->getDefaultController(0)) . '\\' . $this->className($this->getRequest()))
                        and method_exists(
                                ucfirst($this->getDefaultController(0)) . '\\' . $this->className($this->getRequest()), $this->methodName($this->getDefaultController(3))
                        )
                ) {
                    $this->setClassName(ucfirst($this->getDefaultController(0)) . '\\' . $this->className($this->getRequest()));
                    $this->setMethodName($this->methodName($this->getDefaultController(3)));
                } else {
                    //params
                    $this->setParams($this->getRequest());
                    $this->setClassName($this->getDefaultController(1));
                    $this->setMethodName($this->methodName($this->getDefaultController(2)));
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
        if (null !== $this->getRequestKey($value)) {
            if (count($this->getRequest()) > $value) {
                for ($i = $value; $i < count($this->getRequest()); $i++) {
                    $this->setParams($this->getRequestKey($i));
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
        if (class_exists($this->getClassName())) {
            if (method_exists($this->getClassName(), $this->getMethodName())) {
                $this->setNewObject($this->getClassName());
                $this->getNewObject($this->getClassName())->init($this->getDi());
                $this->getNewObject($this->getClassName())->before();
                $this->getNewObject($this->getClassName())->{$this->getMethodName()}($params);
                $this->getNewObject($this->getClassName())->after();
            }
        }
    }

    /**
     * @param $className
     */
    public function setNewObject($className)
    {
        $this->newObject[$className] = new $className;
    }

    /**
     * @param $className
     * @return mixed
     */
    public function getNewObject($className)
    {
        return $this->newObject[$className];
    }

    /**
     * @return IContainer
     */
    public function getDi(): IContainer
    {
        return $this->di;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @param mixed $methodName
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param $defaultController
     */
    public function setDefaultController($defaultController)
    {
        $this->defaultController = $defaultController;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getDefaultController($key)
    {
        return $this->defaultController[$key];
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRequestKey($key)
    {
        return $this->request[$key];
    }

    /**
     * @param $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params[] = $params;
    }

}
