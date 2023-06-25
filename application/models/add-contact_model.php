<?php

class AddContactModel extends \core\Model
{
    private $contacts;

    public function __construct($CONFIG){
        $this->contacts = $CONFIG->getContacts();
    }

    //***** Выход пользователя из системы *****/
    public function run(){
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        echo $this->contacts->addContact($_GET['contact'], $email) ;
    }
}