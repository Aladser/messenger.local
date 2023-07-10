<?php

/** Добавить контакт(чат) с пользователем */
class ChatsModel extends \core\Model
{
    private $userTable;

    public function __construct($CONFIG){
        $this->userTable = $CONFIG->getUsers();
    }

    public function run(){
        session_start();
        $userEmail = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $publicUsername = $this->userTable->getPublicUsername($userEmail);
        return ['user-email' => $userEmail, 'publicUsername' => $publicUsername];
    }
}