<?php

// Контакты пользователя
class GetContactModel extends \core\Model
{
    private $contactsTable;
    private $usersTable;
    private $messageTable;

    public function __construct($CONFIG)
    {
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    public function run()
    {
        $userHostName = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];  // имя клиента-хоста
        $userId = $this->usersTable->getUserId($userHostName);                              // id клиента-хоста
        $contactId = $this->usersTable->getUserId($_POST['contact']);                        // id клиента-контакта

        // добавляется контакт, если не существует
        $isContact = $this->contactsTable->existsContact($contactId, $userId);
        if (!$isContact) {
            $this->contactsTable->addContact($contactId, $userId);
            $chatId = $this->messageTable->getDialogId($userId, $contactId); // создается диалог, если не существует
            $contactName = $this->usersTable->getPublicUsername($contactId);
            $userData = ['username'=>$contactName, 'chat_id'=>$chatId, 'isnotice'=>0];
        } else {
            $contact = $this->contactsTable->getContact($userId, $contactId);
            $userData = ['username'=>$contact[0]['username'],
                'chat_id'=>$contact[0]['chat_id'],
                'isnotice'=>$contact[0]['isnotice']
            ];
        }
        echo json_encode($userData);
    }
}
