<?php
namespace core;

abstract class Model
{
    abstract public function run();

    public function getUserMail()
    {
        session_start();
        $email = isset($_COOKIE['email']) ?  $_COOKIE['email'] : $_SESSION['email'];
        return   $email;
    }
}
