<?php

/** Кнопка назад*/
class BackModel extends \core\Model
{
    public function run(){
        session_start();
        setcookie("email", "", time()-3600, '/');
        setcookie("auth", "", time()-3600, '/');
        session_destroy();
        header("Location: /main");
    }
}