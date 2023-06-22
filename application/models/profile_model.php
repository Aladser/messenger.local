<?php

/** Данные о профиле текущего пользователя */
class ProfileModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = $CONFIG->getUsers();
    }

    public function run(){
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        return $this->users->getUsersData($email);
    }
}

