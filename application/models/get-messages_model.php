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
        // диалоги
        if(isset($_POST['contact'])){
            $userHostName = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];  // имя клиента-хоста
            $userId = $this->usersTable->getUserId($userHostName);                              // id клиента-хоста
            $contactId = $this->usersTable->getUserId($_POST['contact']);                        // id клиента-контакта
            $this->contactsTable->addContact($contactId, $userId);                              // добавить контакт, если не является им
            $chatId = $this->messageTable->getDialogId($userId, $contactId);
            $rslt = ['chatId' => $chatId, 'type'=>'dialog', 'messages' => $this->messageTable->getMessages($chatId)];                          
        }
        // групповые чаты
        else if(isset($_POST['discussionid'])){
            $chatId = $_POST['discussionid'];
            $rslt = ['chatId' => $chatId, 'type'=>'discussion', 'messages' => $this->messageTable->getMessages($chatId)];
        } 
        echo json_encode($rslt);
    }
}