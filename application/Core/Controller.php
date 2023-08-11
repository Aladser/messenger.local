<?php

namespace Aladser\Core;

use Aladser\Core\DB\DBCtl;

abstract class Controller
{
    public $view;

    public abstract function index($getArgs);

    public function __construct(DBCtl $dbCtl= null)
    {
        $this->view = new View();
        $this->dbCtl = $dbCtl;
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
