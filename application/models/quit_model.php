<?php

class QuitModel extends \core\Model
{
    //***** Выход пользователя из системы *****/
    public function run(){
        session_start();
        setcookie("email", "", time()-3600, '/');
        setcookie("auth", "", time()-3600, '/');
        session_destroy();
        header('Location: /Main');
    }
}

