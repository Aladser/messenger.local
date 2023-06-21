<?php

class QuitModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = $CONFIG->getUsers();
    }

    //***** Выход пользователя из системы *****/
    public function run(){
        session_start();
        setcookie("email", "", time()-3600, '/');
        setcookie("auth", "", time()-3600, '/');
        session_destroy();
        header('Location: /Main');
    }
}

