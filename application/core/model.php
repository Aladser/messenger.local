<?php

namespace Aladser\Core;

abstract class Model
{
    abstract public function run();

    public function getUserMail(): string
    {
        session_start();
        return $_COOKIE['email'] ?? $_SESSION['email'];
    }

    /** проверка CSRF-токена */
    public static function checkCSRF($clientCSRF, $serverSCRF): bool
    {
        return $clientCSRF === $serverSCRF;
    }
}
