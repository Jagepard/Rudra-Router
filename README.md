# Rudra-Router

Маршруты определяются по соглашению в файле
app/config/Routing.php

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

    $this->app->set('{?url}',
        ['App\\Main\\Controller\\MainController', 'actionIndex']
    );

Простейший POST-роут:

    $this->app->set('',
        ['App\\Main\\Controller\\MainController', 'addPost'], 'POST'
    );
