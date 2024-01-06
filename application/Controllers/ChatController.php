<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatEntity;
use App\Models\ContactEntity;
use App\Models\GroupContactEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;

use function App\config;

/** контроллер чата */
class ChatController extends Controller
{
    private UserEntity $users;
    private ChatEntity $chats;
    private MessageEntity $messages;
    private ContactEntity $contacts;
    private GroupContactEntity $groupContacts;
    private string $authUserEmail;
    private int $authUserId;

    public function __construct()
    {
        parent::__construct();
        $this->chats = new ChatEntity();
        $this->contacts = new ContactEntity();
        $this->groupContacts = new GroupContactEntity();
        $this->messages = new MessageEntity();
        $this->users = new UserEntity();

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
            $contacts[$i]['photo'] = UserController::getAvatarImagePath(null, 'chat');
        }
        $data['contacts'] = $contacts;

        // группы пользователя
        $groups = $this->chats->getDiscussions($this->authUserId);

        for ($i = 0; $i < count($groups); ++$i) {
            $discussionId = $groups[$i]['chat'];
            $creatorId = $this->chats->getDiscussionCreatorId($discussionId);
            $groups[$i]['members'] = $this->groupContacts->get($discussionId);
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

    public function getMessages()
    {
        // диалоги
        if (isset($_POST['contact'])) {
            $contact = htmlspecialchars($_POST['contact']);
            $contactId = $this->users->getIdByName($contact);
            $chatId = $this->chats->getDialogId($this->authUserId, $contactId);
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

    public function createGroup()
    {
        $groupId = $this->chats->add('discussion', $this->authUserId);
        $group_name = $this->chats->getName($groupId);

        $this->contacts->add($groupId, $this->authUserId);
        $authorPublicName = $this->users->getPublicUsername($this->authUserId);

        echo json_encode([
            'id' => $groupId,
            'name' => $group_name,
            'author' => $authorPublicName,
        ]);
    }

    public function editNoticeShow()
    {
        $username = htmlspecialchars($_POST['username']);
        $notice = htmlspecialchars($_POST['notice']);
        $notice = intval($notice);
        $chatid = htmlspecialchars($_POST['chat_id']);
        $isEdited = $this->chats->setNoticeShow($chatid, $this->authUserId, $notice);

        echo json_encode(['responce' => $isEdited]);
    }
}
