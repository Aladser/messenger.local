<?php

/** Поиск контактов пользователя */
class FindContactsModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = $CONFIG->getUsers();
    }

    public function run(){
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        echo json_encode($this->users->getUsers($_GET['userphrase'], $email));
    }
}

