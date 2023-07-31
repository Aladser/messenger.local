<?php

namespace Aladser\Core;

use Exception;

abstract class Model
{
    abstract public function run();

    /** получить почту пользователя из сессии или куки */
    public function getUserMailFromClient()
    {
        session_start();
        if (isset($_COOKIE['email'])) {
            return $_COOKIE['email'];
        } elseif (isset($_SESSION['email'])) {
            return $_SESSION['email'];
        } else {
            return null;
        }
    }

    /** создать CSRF-токен
     * @throws Exception
     */
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
