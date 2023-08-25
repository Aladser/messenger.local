<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\DB\DBCtl;
use Aladser\Models\UsersDBTableModel;
use Aladser\Models\ContactsDBTableModel;
use Aladser\Models\MessageDBTableModel;

/** контроллер чата */
class ChatController extends Controller
{
    private UsersDBTableModel $users;
    private MessageDBTableModel $messages;

    public function __construct(DBCtl $dbCtl = null)
    {
        parent::__construct($dbCtl);
        $this->users = $dbCtl->getUsers();
        $this->messages = $dbCtl->getMessageDBTable();
    }

    public function index()
    {
        $userEmail = Controller::getUserMailFromClient();
        $publicUsername = $this->users->getPublicUsernameFromEmail($userEmail);
        $userId = $this->users->getUserId($userEmail);

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath . $userEmail . '*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $data['csrfToken'] = Controller::createCSRFToken();
        $this->view->generate('template_view.php', 'chat_view.php', 'chat.css', '', 'Чат', $data);
    }

    public function createGroup()
    {
        $userEmail = Controller::getUserMailFromClient();
        $userId = $this->users->getUserId($userEmail);
        $groupId = $this->messages->createDiscussion($userId);
        echo json_encode($groupId);
    }

    public function editNoticeShow()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';
            return;
        }

        $username = htmlspecialchars($_POST["username"]);
        $userId = $this->users->getUserId($username);

        $notice = htmlspecialchars($_POST["notice"]);
        $notice = intval($notice);

        $chatid = htmlspecialchars($_POST["chat_id"]);
        echo json_encode(['responce' => $this->messages->setNoticeShow($chatid, $userId, $notice)]);
    }

    public function getGroups()
    {
        $username = Controller::getUserMailFromClient();
        $userId = $this->users->getUserId($username);
        echo json_encode($this->messages->getDiscussions($userId));
    }

    public function getMessages()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';
            return;
        } 

        // диалоги
        if (isset($_POST['contact'])) {
            $contact = htmlspecialchars($_POST['contact']);
            $userHostName = Controller::getUserMailFromClient();
            $userId = $this->users->getUserId($userHostName);
            $contactId = $this->users->getUserId($contact);
            $chatId = $this->messages->getDialogId($userId, $contactId);
            $type = 'dialog';
        } elseif (isset($_POST['discussionid'])) {
            // групповые чаты
            $chatId = htmlspecialchars($_POST['discussionid']);
            $type = 'discussion';
        }

        $messages = [
            'current_chat' => $chatId, 'type' => $type,
            'messages' => $this->messages->getMessages($chatId)
        ];
        echo json_encode($messages);
    }
}
