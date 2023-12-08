<?php

namespace Aladser\Core;

use Aladser\Controllers\MainController;

class Route
{
    // специфичные роуты
    // роут - действие, $specificRoutes[роут] - контроллер
    private static $specificRoutes = [
     'login' => 'UserController',
     'register' => 'UserController',
     'update' => 'UserController',
     'is_nickname_unique' => 'UserController',
     'verify-email' => 'UserController',
     'upload-file' => 'MainController',
    ];

    public static function start()
    {
        session_start();

        // ---URL---
        // вырезается "/"
        $url = mb_substr($_SERVER['REQUEST_URI'], 1);
        // вырезаются get-аргументы
        $url = explode('?', $url)[0];

        // ---имя контроллера и метод---
        if (array_key_exists($url, self::$specificRoutes)) {
            $controller_name = self::$specificRoutes[$url];
            $action = self::convertName($url);
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
            $controller_name = self::convertName($controller_name).'Controller';
        }

        // ---авторизация сохраняется в куки и сессии. Если авторизация есть, то / => /chat---
        if ($controller_name === 'MainController'
            && $action === 'index'
            && (isset($_SESSION['auth']) || isset($_COOKIE['auth']))
            && !isset($_GET['logout'])
        ) {
            $controller_name = 'ChatController';
        }
        // ---редирект /chats или /profile без авторизации => /---
        if (($controller_name === 'ChatController')
            && !(isset($_SESSION['auth']) || isset($_COOKIE['auth']))
        ) {
            $controller_name = 'MainController';
        }

        // --- подключение контроллера и вызов метода контроллера---
        $controller_path = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php';
        // echo $controller_name.' '.$action;
        try {
            // подключение класса контроллера
            if (!file_exists($controller_path)) {
                throw new \Exception();
            }
            require_once $controller_path;
            $controller_name = '\\Aladser\\Controllers\\'.$controller_name;
            $controller = new $controller_name();
            // вызов метода
            if (!method_exists($controller, $action)) {
                throw new \Exception();
            }
            $controller->$action();
        } catch (\Exception $err) {
            $controller = new MainController();
            $controller->error404();
        }
    }

    private static function convertName($name)
    {
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        return $name;
    }
}
