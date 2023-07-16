<?php

// Контакты пользователя
class GetContactsModel extends \core\Model
{
    private $contactsTable;
    private $usersTable;

    public function __construct($CONFIG){
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run(){
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $userId = $this->usersTable->getUserId($email);
        echo json_encode($this->contactsTable->getContacts($userId));
    }
}