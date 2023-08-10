<?php

namespace Aladser\Core;

class Controller
{
    public $view;
    public $model;

    public function __construct($modelName = null)
    {
        $this->view = new View();
        $this->model = is_null($modelName) ? null : $modelName;
    }

    /** получить почту пользователя из сессии или куки */
    public static function getUserMailFromClient()
    {
        if (isset($_COOKIE['email'])) {
            return $_COOKIE['email'];
        } elseif (isset($_SESSION['email'])) {
            return $_SESSION['email'];
        } else {
            return null;
        }
    }

    /** создать CSRF-токен */
    public static function createCSRFToken(): string
    {
        $csrfToken = hash('gost-crypto', random_int(0, 999999));
        $_SESSION["CSRF"] = $csrfToken;
        return $csrfToken;
    }

    /** проверить CSRF-токен */
    public static function checkCSRF($clientCSRF, $serverSCRF): bool
    {
        return $clientCSRF === $serverSCRF;
    }
}
