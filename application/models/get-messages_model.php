<?php

/**
 * Список сообщений от конкретного пользователя
 */
class GetMessagesModel extends \core\Model
{
    private $usersTable;
    private $contactsTable;
    private $messageTable;

    public function __construct($CONFIG){
        $this->usersTable = $CONFIG->getUsers();
        $this->contactsTable = $CONFIG->getContacts();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    // получить список сообщений чата
    public function run(){
        session_start();
        if(isset($_GET['contact'])){
            $userHostName = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];  // имя клиента-хоста
            $userId = $this->usersTable->getUserId($userHostName);                              // id клиента-хоста
            $contactId = $this->usersTable->getUserId($_GET['contact']);                        // id клиента-контакта
            $this->contactsTable->addContact($contactId, $userId);                              // добавить контакт, если не является им
            $chatId = $this->messageTable->getDialogId($userId, $contactId);
            $rslt = ['chatId' => $chatId, 'messages' => $this->messageTable->getMessages($chatId)];                     
        }
        else{

        }
        echo json_encode($rslt);
    }
}