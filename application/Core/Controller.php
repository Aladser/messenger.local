<?php

namespace Aladser\Core;

abstract class Controller
{
    public View $view;

    public function __construct()
    {
        $this->view = new View();
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
        $_SESSION['CSRF'] = $csrfToken;

        return $csrfToken;
    }
}
