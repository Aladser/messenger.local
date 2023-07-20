<?php

// Контакты пользователя
class EditNoticeShowModel extends \core\Model
{
    private $contactsTable;
    private $usersTable;

    public function __construct($CONFIG){
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run(){
        
    }
}