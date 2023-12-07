<?php

namespace Aladser\Controllers;

use Aladser\Core\Config;
use Aladser\Core\Controller;
use Aladser\Models\MessageEntity;
use Aladser\Models\UserEntity;

/** контроллер чата */
class ChatController extends Controller
{
    private UserEntity $users;
    private MessageEntity $messages;

    public function __construct()
    {
        parent::__construct();
        $this->messages = new MessageEntity();
        $this->users = new UserEntity();
    }

    public function index()
    {
        $userEmail = Config::getEmailFromClient();
        $publicUsername = $this->users->getPublicUsernameFromEmail($userEmail);
        $userId = $this->users->getUserId($userEmail);

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath.$userEmail.'*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $data['csrfToken'] = Config::createCSRFToken();
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

        $username = htmlspecialchars($_POST['username']);
        $userId = $this->users->getUserId($username);

        $notice = htmlspecialchars($_POST['notice']);
        $notice = intval($notice);

        $chatid = htmlspecialchars($_POST['chat_id']);
        echo json_encode(['responce' => $this->messages->setNoticeShow($chatid, $userId, $notice)]);
    }

    public function getGroups()
    {
        $username = Config::getEmailFromClient();
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
            $userHostName = Config::getEmailFromClient();
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
            'messages' => $this->messages->getMessages($chatId),
        ];
        echo json_encode($messages);
    }
}
