<?php

namespace Aladser\Core;

use Aladser\Core\DB\DBCtl;
use \Aladser\Controllers\Page404Controller;

class Route
{
    public static function start()
    {
        session_start();

        // контроллер и аргументы
        $routes = mb_substr($_SERVER['REQUEST_URI'], 1);
        $routes = explode('?', $routes)[0];
        $routesArr = explode('/', $routes);
        // выбор контроллера
        $controller_name = !empty($routes) ? ucfirst($routesArr[0]) : 'main';
        // получение имени метода
        if (count($routesArr) > 1) {
            $action = $routesArr[1];
            $actionArr = explode('-', $action);
            for ($i = 1; $i<count($actionArr); $i++) {
                $actionArr[$i] = ucfirst($actionArr[$i]);
            }
            $action = implode('', $actionArr);
        } else {
            $action = 'index';
        }

        // преобразовать url в название класса
        $controller_name = str_replace('-', ' ', $controller_name);
        $controller_name = ucwords($controller_name);
        $controller_name = str_replace(' ', '', $controller_name);

        // авторизация сохраняется в куки и сессии. Если авторизация есть, то messenger.local -> messenger.local/chat
        if ($controller_name === 'Main'
            && (isset($_SESSION['auth']) || isset($_COOKIE['auth']))
            && !isset($_GET['logout'])
        ) {
            $controller_name = 'Chat';
        }

        // редирект /chats или /profile без авторизации -> messenger.local
        if (($controller_name === 'Chat'|| $controller_name === 'Profile')
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
            $controller = new Page404Controller();
            $controller->index();
            return;
        }

        // вызов метода
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller = new Page404Controller();
            $controller->index();
        }
        
    }
}
