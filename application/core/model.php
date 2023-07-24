<?php

namespace Aladser\Core;

abstract class Model
{
    abstract public function run();

    public function getUserMail()
    {
        session_start();
        return $_COOKIE['email'] ?? $_SESSION['email'];
    }
}
