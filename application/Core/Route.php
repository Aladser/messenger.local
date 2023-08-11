<?php

namespace Aladser\Core;

use Aladser\Core\DB\DBCtl;
use Aladser\Controllers\Page404Controller;

class Route
{
    public static function start()
    {
        session_start();

        // контроллер и действие
        if (array_key_exists('REDIRECT_URL', $_SERVER)) {
            $routes = mb_substr($_SERVER['REDIRECT_URL'], 1);
            $controller_name = !empty($routes) ? ucfirst($routes) : 'main';
            // преобразовать url в название класса
            $controller_name = str_replace('-', ' ', $controller_name);
            $controller_name = ucwords($controller_name);
            $controller_name = str_replace(' ', '', $controller_name);
        } else {
            $controller_name = 'Main';
        }
        $action = 'index';

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

        // добавляем префиксы
        $controller_name = $controller_name.'Controller';

        // подцепляем файл с классом контроллера
        $controller_path =
            dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php';

        if (file_exists($controller_path)) {
            require_once($controller_path);
            // создаем контроллер
            $controller_name = "\\Aladser\\Controllers\\$controller_name";
            $controller = new $controller_name(
                new DBCtl(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB)
            );
        } else {
            $controller_name = "\\Aladser\\Controllers\\Page404Controller";
            $controller = new $controller_name();
        }

<<<<<<< HEAD
        // создаем модель, если существует
        $model = null;
        if (file_exists($model_path)) {
            $model_name = "\\Aladser\\Models\\$model_name";
            $model = new $model_name(new DBCtl(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB));
        }

        // создаем контроллер
        $controller_name = "\\Aladser\\Controllers\\$controller_name";
        $controller = new $controller_name(new DBCtl(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB));

=======
        $action = $action_name;
>>>>>>> refs/remotes/origin/main
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller_name = "\\Aladser\\Controllers\\Page404Controller";
            $controller = new $controller_name();
        }
    }
}
