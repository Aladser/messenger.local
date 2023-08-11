<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер получения сообщений открытого чата */
class GetMessagesController extends Controller
{
    public function actionIndex()
    {
        $chatId = null;
        $type = null;
        // диалоги
        if (isset($_POST['contact'])) {
            $userHostName = Controller::getUserMailFromClient();
            $userId = $this->dbCtl->getUsers()->getUserId($userHostName);
            $contactId = $this->dbCtl->getUsers()->getUserId($_POST['contact']);
            $chatId = $this->dbCtl->getMessageDBTable()->getDialogId($userId, $contactId);
            $type = 'dialog';
        } elseif (isset($_POST['discussionid'])) {
            // групповые чаты
            $chatId = $_POST['discussionid'];
            $type = 'discussion';
        }

        $messages = ['current_chat' => $chatId, 'type' => $type, 'messages' => $this->dbCtl->getMessageDBTable()->getMessages($chatId)];
        echo json_encode($messages);
    }
}
