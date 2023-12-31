<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ContactEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;

use function App\config;

/** контроллер чата */
class ChatController extends Controller
{
    private UserEntity $users;
    private MessageEntity $messages;
    private ContactEntity $contacts;
    private string $authUserEmail;
    private int $authUserId;

    public function __construct()
    {
        parent::__construct();
        $this->messages = new MessageEntity();
        $this->users = new UserEntity();
        $this->contacts = new ContactEntity();
        $this->authUserEmail = UserController::getAuthUserEmail();
        $this->authUserId = $this->users->getIdByName($this->authUserEmail);
    }

    public function index()
    {
        $userId = $this->users->getIdByName($this->authUserEmail);
        $publicUsername = $this->users->getPublicUsername($userId);
        // head
        $websocket = config('WEBSOCKET_ADDR');
        $csrf = MainController::createCSRFToken();
        $head = "<meta name='websocket' content=$websocket>";
        $head .= "<meta name='csrf' content=$csrf>";

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath.$this->authUserEmail.'*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $this->authUserEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $this->authUserId;

        // контакты пользователя
        $contacts = $this->contacts->getUserContacts($this->authUserId);
        // указание путей до аватарок
        for ($i = 0; $i < count($contacts); ++$i) {
            if ($contacts[$i]['photo'] === 'ava_profile.png' || empty($contacts[$i]['photo'])) {
                $contacts[$i]['photo'] = config('SITE_ADDRESS_ORIGIN').'/application/images/ava.png';
            } else {
                $contacts[$i]['photo'] = config('SITE_ADDRESS_ORIGIN').'/application/data/profile_photos/'.$contacts[$i]['photo'];
            }
        }
        $data['contacts'] = $contacts;

        // группы пользователя
        $groups = $this->messages->getDiscussions($this->authUserId);
        for ($i = 0; $i < count($groups); ++$i) {
            $discussionId = $groups[$i]['chat'];
            $creatorId = $this->messages->getDiscussionCreatorId($discussionId);
            $groups[$i]['members'] = $this->contacts->getGroupContacts($discussionId);
        }
        $data['groups'] = $groups;

        $this->view->generate(
            'Меcсенджер',
            'template_view.php',
            'chat_view.php',
            $head,
            'chat.css',
            [
                'ServerRequest.js',
                'ChatWebsocket.js',
                'contex-menu/ContexMenu.js',
                'contex-menu/MessageContexMenu.js',
                'contex-menu/ContactContexMenu.js',
                'Containers/TemplateContainer.js',
                'Containers/MessageContainer.js',
                'Containers/ContactContainer.js',
                'Containers/GroupContainer.js',
                'chat.js',
            ],
            $data
        );
    }

    public function createGroup()
    {
        $groupId = $this->messages->createDiscussion($this->authUserId);
        echo json_encode($groupId);
    }

    public function editNoticeShow()
    {
        $username = htmlspecialchars($_POST['username']);
        $notice = htmlspecialchars($_POST['notice']);
        $notice = intval($notice);
        $chatid = htmlspecialchars($_POST['chat_id']);

        echo json_encode(['responce' => $this->messages->setNoticeShow($chatid, $this->authUserId, $notice)]);
    }

    public function getMessages()
    {
        // диалоги
        if (isset($_POST['contact'])) {
            $contact = htmlspecialchars($_POST['contact']);
            $contactId = $this->users->getIdByName($contact);
            $chatId = $this->messages->getDialogId($this->authUserId, $contactId);
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
