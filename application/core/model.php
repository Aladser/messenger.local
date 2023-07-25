<?php

namespace Aladser\Core;

use Exception;

abstract class Model
{
    abstract public function run();

    public function getUserMail(): string
    {
        session_start();
        return $_COOKIE['email'] ?? $_SESSION['email'];
    }

    /** создать CSRF-токен
     * @throws Exception
     */
    public static function createCSRFToken(): string
    {
        session_start();
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
