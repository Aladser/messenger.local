<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MessageEntity;
use App\Models\UserEntity;

use function App\config;

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
        $userEmail = UserController::getEmailFromClient();
        $publicUsername = $this->users->getPublicUsernameFromEmail($userEmail);
        $userId = $this->users->getUserId($userEmail);

        // head
        $websocket = config('WEBSOCKET_ADDR');
        $csrf = MainController::createCSRFToken();
        $head = "<meta name='websocket' content=$websocket>";
        $head .= "<meta name='csrf' content=$csrf>";

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath.$userEmail.'*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $this->view->generate(
            'Месенджер',
            'template_view.php',
            'chat_view.php',
            $head,
            'chat.css',
            [
                'ChatWebsocket.js',
                'contex-menu/ContexMenu.js',
                'contex-menu/MessageContexMenu.js',
                'contex-menu/ContactContexMenu.js',
                'chat/TemplateContainer.js',
                'chat/MessageContainer.js',
                'chat/ContactContainer.js',
                'chat/GroupContainer.js',
                'chat/chat.js',
            ],
            $data
        );
    }

    public function createGroup()
    {
        $userEmail = UserController::getEmailFromClient();
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
        $username = UserController::getEmailFromClient();
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
            $userHostName = UserController::getEmailFromClient();
            $userId = $this->users->getUserId($userHostName);
            $contactId = $this->users->getUserId($contact);
            $chatId = $this->messages->getDialogId($userId, $contactId);
            $type = 'dialog';
        } elseif (isset($_POST['discussionid'])) {
            // групповые чаты
            $chatId = htmlspecialchars($_POST['discussionid']);
            $type = 'discussion';
        } else {
            return;
        }

        $messages = [
            'current_chat' => $chatId,
            'type' => $type,
            'messages' => $this->messages->getMessages($chatId),
        ];
        echo json_encode($messages);
    }
}
