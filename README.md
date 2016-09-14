# Rudra-Router

При использовании класса Router (ручная маршрутизация)
Маршруты определяются по соглашению в файле
app/config/Routing.php

Маршрутизатор иницианализируется в app/public/index.php

    $app = new \App\Config\Routing(
        new \Rudra\Router($di)
    );

Простейший GET-роут:

    $this->app->set('',
        function () 
        {
            echo "Hello World!!!";
        }
    );

GET-роут к экшену контроллера

    $this->app->set('',
        ['App\\Main\\Controller\\MainController', 'actionIndex']
    );

GET-роут к экшену контроллера в котором указаны параметры, которые можно получить в виде массива аргументов

    $this->app->set('{url}',
        ['App\\Main\\Controller\\MainController', 'actionIndex']
    );

Простейший POST-роут:

    $this->app->set('',
        ['App\\Main\\Controller\\MainController', 'addPost'], 'POST'
    );

При использовании класса Autorouter (автоматическая маршрутизация)
Маршруты не определяются, маршрутизация происходит автоматически
исходя из данных адресной строки

Маршрутизатор иницианализируется в app/public/index.php

    $di->set('router', new \Core\Router($di));
    $di->get('router')->run(\Config\Config::class, $di);

    '/'                  --- [DefaultModule][DefaultController][DefaultAction]			
    '/Controller'        --- [DefaultModule]Controller[DefaultAction]		
    '/Action'            --- [DefaultModule][DefaultController]Action		
    '/Controller/Action' --- [DefaultModule]Controller/Action	
    
    '/Controller/Action' --- Module/Controller[DefaultAction]	
    'Module/Controller/Action' --- Module/Controller/Action



Маршрут по умолчанию указывается в файле app/config/Config.php
в статическом свойстве $defaultController

    public static $defaultController = [
        //Базовый модуль
        'main',
        //Базовый Контроллер
        'Main\\MainController',
        //Базовый экшн
        'index',
        // Имя Метода по умолчанию для всех контроллеров
        'index'
    ];
