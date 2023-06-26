<?php

// Контакты пользователя
class GetContactsModel extends \core\Model
{
    private $contacts;

    public function __construct($CONFIG){
        $this->contacts = $CONFIG->getContacts();
    }

    public function run(){
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        echo json_encode($this->contacts->getContacts($email));
    }
}