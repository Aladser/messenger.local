<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\DB\DBCtl;

/** контроллер чата */
class ChatController extends Controller
{
    public function __construct(DBCtl $dbCtl = null)
    {
        parent::__construct($dbCtl);
    }

    public function index()
    {
        $userEmail = Controller::getUserMailFromClient();
        $publicUsername = $this->dbCtl->getUsers()->getPublicUsernameFromEmail($userEmail);
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath . $userEmail . '*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $data['csrfToken'] = Controller::createCSRFToken();
        $this->view->generate('template_view.php', 'chat_view.php', 'chat.css', 'chat.js', 'Чат', $data);
    }

    public function createGroup()
    {
        $userEmail = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);
        $groupId = $this->dbCtl->getMessageDBTable()->createDiscussion($userId);
        echo json_encode($groupId);
    }

    public function editNoticeShow()
    {
        $userId = $this->dbCtl->getUsers()->getUserId($_POST["username"]);
        $notice = intval($_POST["notice"]);
        echo $this->dbCtl->getMessageDBTable()->setNoticeShow($_POST["chat_id"], $userId, $notice);
    }

    public function getGroups()
    {
        $username = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($username);
        echo json_encode($this->dbCtl->getMessageDBTable()->getDiscussions($userId));
    }

    public function getMessages()
    {
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

        $messages = [
            'current_chat' => $chatId, 'type' => $type,
            'messages' => $this->dbCtl->getMessageDBTable()->getMessages($chatId)
        ];
        echo json_encode($messages);
    }
}
