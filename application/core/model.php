<?php

namespace core;

abstract class Model
{
    abstract public function run();
    public function getUserMail(){
        session_start();
        return  isset($_COOKIE['email']) ?  $_COOKIE['email'] : $_SESSION['email'];
    }
}