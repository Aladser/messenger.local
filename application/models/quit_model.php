<?php

class QuitModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = new \core\db\UsersDBTableModel($CONFIG->getDBQueryCtl());
    }

    //***** Выход пользователя из системы *****/
    public function getData(){
        session_start();
        setcookie("email", "", time()-3600, '/');
        setcookie("auth", "", time()-3600, '/');
        session_destroy();
        header('Location: /Main');
    }
}

