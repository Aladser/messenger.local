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
        // проверка CSRF
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo 'Подмена URL-адреса';
            return;
        }


        $username = htmlspecialchars($_POST["username"]);
        $userId = $this->dbCtl->getUsers()->getUserId($username);
        $notice = htmlspecialchars($_POST["notice"]);
        $notice = intval($notice);
        $chatid = htmlspecialchars($_POST["chat_id"]);
        echo $this->dbCtl->getMessageDBTable()->setNoticeShow($chatid, $userId, $notice);
    }

    public function getGroups()
    {
        $username = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($username);
        echo json_encode($this->dbCtl->getMessageDBTable()->getDiscussions($userId));
    }

    public function getMessages()
    {
        // проверка CSRF
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo 'Подмена URL-адреса';
            return;
        } 

        // диалоги
        if (isset($_POST['contact'])) {
            $contact = htmlspecialchars($_POST['contact']);
            $userHostName = Controller::getUserMailFromClient();
            $userId = $this->dbCtl->getUsers()->getUserId($userHostName);
            $contactId = $this->dbCtl->getUsers()->getUserId($contact);
            $chatId = $this->dbCtl->getMessageDBTable()->getDialogId($userId, $contactId);
            $type = 'dialog';
        } elseif (isset($_POST['discussionid'])) {
            // групповые чаты
            $chatId = htmlspecialchars($_POST['discussionid']);
            $type = 'discussion';
        }

        $messages = [
            'current_chat' => $chatId, 'type' => $type,
            'messages' => $this->dbCtl->getMessageDBTable()->getMessages($chatId)
        ];
        echo json_encode($messages);
    }
}
