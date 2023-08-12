<?php

namespace Aladser\Core;

use Aladser\Core\DB\DBCtl;
use Aladser\Controllers\Page404Controller;

class Route
{
    public static function start()
    {
        session_start();

        // контроллер и аргументы
        $getArgs = null;
        if (array_key_exists('REDIRECT_URL', $_SERVER)) {
            $routes = mb_substr($_SERVER['REDIRECT_URL'], 1);
            $routesArr = explode('/', $routes);
            // выбор контроллера
            $controller_name = !empty($routes) ? ucfirst($routesArr[0]) : 'main';
            // передача действия как GET-параметра
            if (count($routesArr) > 1) {
                $_GET['action'] = $routesArr[1];
            }

            // преобразовать url в название класса
            $controller_name = str_replace('-', ' ', $controller_name);
            $controller_name = ucwords($controller_name);
            $controller_name = str_replace(' ', '', $controller_name);
        } else {
            $controller_name = 'Main';
        }

        // авторизация сохраняется в куки и сессии. Если авторизация есть, то messenger.local -> messenger.local/chats
        if ($controller_name === 'Main'
            && (isset($_SESSION['auth']) || isset($_COOKIE['auth']))
            && !isset($_GET['logout'])
        ) {
            $controller_name = 'Chats';
        }

        // редирект /chats или /profile без авторизации -> messenger.local
        if (($controller_name === 'Chats'|| $controller_name === 'Profile')
            && !(isset($_SESSION['auth']) || isset($_COOKIE['auth']))
        ) {
            $controller_name = 'Main';
        }

        // создаем контроллер
        $controller_name = $controller_name.'Controller';
        $controller_path = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php';
        if (file_exists($controller_path)) {
            require_once($controller_path);
            $controller_name = "\\Aladser\\Controllers\\$controller_name";
            $controller = new $controller_name(
                new DBCtl(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB)
            );
        } else {
            $controller_name = "\\Aladser\\Controllers\\Page404Controller";
            $controller = new $controller_name();
        }

        $controller->index();
    }
}
