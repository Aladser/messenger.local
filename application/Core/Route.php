<?php

namespace Aladser\Core;

use Aladser\Controllers\MainController;

class Route
{
    public static function start()
    {
        session_start();

        // ---URL---
        // вырезается "/"
        $url = mb_substr($_SERVER['REQUEST_URI'], 1);
        // вырезаются get-аргументы
        $url = explode('?', $url)[0];

        // ---контроллер и метод---
        if ($url === 'login') {
            // страница входа
            $controller_name = 'User';
            $action = 'login';
        } elseif ($url === 'register') {
            // страница регистрации
            $controller_name = 'User';
            $action = 'register';
        } elseif ($url === 'verify-email') {
            // подтверждение почты
            $controller_name = 'User';
            $action = 'verifyEmail';
        } else {
            $routesArr = explode('/', $url);
            // выбор контроллера
            $controller_name = !empty($url) ? ucfirst($routesArr[0]) : 'main';
            // получение имени метода
            if (count($routesArr) > 1) {
                $action = $routesArr[1];
                $actionArr = explode('-', $action);
                for ($i = 1; $i < count($actionArr); ++$i) {
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
        }

        // ---авторизация сохраняется в куки и сессии. Если авторизация есть, то / => /chat---
        if ($controller_name === 'Main'
            && (isset($_SESSION['auth']) || isset($_COOKIE['auth']))
            && !isset($_GET['logout'])
        ) {
            $controller_name = 'Chat';
        }
        // ---редирект /chats или /profile без авторизации => /---
        if (($controller_name === 'Chat' || $controller_name === 'Profile')
            && !(isset($_SESSION['auth']) || isset($_COOKIE['auth']))
        ) {
            $controller_name = 'Main';
        }

        // ---контроллер---
        $controller_name .= 'Controller';
        $controller_path = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php';
        if (file_exists($controller_path)) {
            require_once $controller_path;
            $controller_name = '\\Aladser\\Controllers\\'.$controller_name;
            $controller = new $controller_name();
        } else {
            $controller = new MainController();
            $controller->error404();

            return;
        }

        // ---вызов метода---
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller = new MainController();
            $controller->error404();
        }
    }
}
