<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** список сообщений чата с пользователем*/
class GetMessagesModel extends Model
{
    private $usersTable;
    private $messageTable;

    public function __construct($CONFIG)
    {
        $this->usersTable = $CONFIG->getUsers();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    // получить список сообщений чата
    public function run()
    {
        session_start();
        $chatId = null;
        $type = null;
        // диалоги
        if (isset($_POST['contact'])) {
            $userHostName = Model::getUserMailFromClient();  // имя клиента-хоста
            $userId = $this->usersTable->getUserId($userHostName);                              // id клиента-хоста
            $contactId = $this->usersTable->getUserId($_POST['contact']);                        // id клиента-контакта
            $chatId = $this->messageTable->getDialogId($userId, $contactId);
            $type = 'dialog';
        } elseif (isset($_POST['discussionid'])) {
            // групповые чаты
            $chatId = $_POST['discussionid'];
            $type = 'discussion';
        }

        $messages = ['current_chat' => $chatId, 'type'=>$type, 'messages' => $this->messageTable->getMessages($chatId)];
        echo json_encode($messages);
    }
}
