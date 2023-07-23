<?php
namespace core;

abstract class Model
{
    public abstract function run();

    public function getUserMail()
    {
        session_start();
        return  isset($_COOKIE['email']) ?  $_COOKIE['email'] : $_SESSION['email'];
    }
}
