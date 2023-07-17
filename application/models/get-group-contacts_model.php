<?php

// Контакты пользователя
class GetGroupContactsModel extends \core\Model
{
    private $contactsTable;
    private $usersTable;

    public function __construct($CONFIG){
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run(){
        $discussionId = $_POST['discussionid']; // id группового чата
        echo json_encode($this->contactsTable->getGroupContacts($discussionId));
    }
}