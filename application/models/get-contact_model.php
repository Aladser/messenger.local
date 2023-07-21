<?php

// Контакты пользователя
class GetContactModel extends \core\Model
{
    private $contactsTable;
    private $usersTable;

    public function __construct($CONFIG){
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run(){
        $userHostName = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];  // имя клиента-хоста
        $userId = $this->usersTable->getUserId($userHostName);                              // id клиента-хоста
        $contactId = $this->usersTable->getUserId($_POST['contact']);                        // id клиента-контакта

        $isUser = $this->contactsTable->existsContact($contactId, $userId);
        if($isUser){
            $this->contactsTable->addContact($contactId, $userId);
        }
        $chatId = $this->messageTable->getDialogId($userId, $contactId);
        //$rslt = ['chatId' => $chatId, 'type'=>'dialog', 'messages' => $this->messageTable->getMessages($chatId)]; 
    }
}