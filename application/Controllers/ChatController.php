<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatEntity;
use App\Models\ChatParticipantEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;

use function App\config;

/** контроллер чата */
class ChatController extends Controller
{
    private UserEntity $users;
    private ChatEntity $chats;
    private MessageEntity $messages;
    private ChatParticipantEntity $chatParticipants;
    private string $authUserEmail;
    private int $authUserId;

    public function __construct()
    {
        parent::__construct();
        $this->chats = new ChatEntity();
        $this->chatParticipants = new ChatParticipantEntity();
        $this->messages = new MessageEntity();
        $this->users = new UserEntity();

        $this->authUserEmail = UserController::getAuthUserEmail();
        $this->authUserId = $this->users->getIdByName($this->authUserEmail);
    }

    public function index()
    {
        $contactId = $this->users->getIdByName($this->authUserEmail);
        $publicUsername = $this->users->getPublicUsername($contactId);
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
        $personalChats = $this->chats->getUserPersonalChats($this->authUserId);
        // указание путей до аватарок
        for ($i = 0; $i < count($personalChats); ++$i) {
            $personalChats[$i]['photo'] = UserController::getAvatarImagePath($personalChats[$i]['photo'], 'chat');
        }
        $data['contacts'] = $personalChats;

        // группы пользователя
        $groups = $this->chats->getUserGroupChats($this->authUserId);
        for ($i = 0; $i < count($groups); ++$i) {
            $groupId = $groups[$i]['chat'];
            $groups[$i]['members'] = $this->chatParticipants->getGroupChatMembers($groupId);
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

    // создать чат
    public function add()
    {
        // создание чата в зависимости от типа
        $type = $_POST['type'];
        if ($type !== 'personal' && $type !== 'group') {
            exit('ChatController->add: неверный тип группы');
        }
        $chatId = $this->chats->add($type, $this->authUserId);

        switch ($type) {
            case 'personal':
                $contactName = htmlspecialchars($_POST['username']);
                $contactId = $this->users->getIdByName($contactName);
                $contactPhoto = $this->users->get($contactId, 'photo');
                // создаем участников чата
                $this->chatParticipants->add($chatId, $this->authUserId);
                $this->chatParticipants->add($chatId, $contactId);

                $userData = [
                    'username' => $contactName,
                    'photo' => UserController::getAvatarImagePath($contactPhoto, 'chat'),
                    'chat_id' => $chatId,
                    'isnotice' => 1,
                ];
                echo json_encode($userData);
                break;
            case 'group':
                $group_name = $this->chats->getName($chatId);
                $this->chatParticipants->add($chatId, $this->authUserId);
                $authorPublicName = $this->users->getPublicUsername($this->authUserId);

                echo json_encode([
                    'id' => $chatId,
                    'name' => $group_name,
                    'author' => $authorPublicName,
                ]);
        }
    }

    // удалить чат
    public function remove()
    {
        $type = htmlspecialchars($_POST['type']);
        if ($type !== 'personal' && $type !== 'group') {
            exit('ChatController->remove: неверный тип группы');
        }

        switch ($type) {
            case 'personal':
                $contact_name = htmlspecialchars($_POST['contact_name']);
                $contactId = $this->users->getIdByName($contact_name);
                $chatId = $this->chats->getPersonalChatId($this->authUserId, $contactId);
                break;
            case 'group':
                $group_name = htmlspecialchars($_POST['group_name']);
                $chatId = $this->chats->getGroupChatId($group_name);
        }

        $isDeleted = $this->chats->remove($chatId);
        echo json_encode(['result' => (int) $isDeleted]);
    }

    // сообщения чата
    public function getMessages()
    {
        $type = $_POST['type'];
        if ($type !== 'personal' && $type !== 'group') {
            exit('ChatController->add: неверный тип группы');
        }

        $chatName = htmlspecialchars($_POST['chat_name']);
        switch ($type) {
            case 'personal':
                $contactId = $this->users->getIdByName($chatName);
                $chatId = $this->chats->getPersonalChatId($this->authUserId, $contactId);
                break;
            case 'group':
                $chatId = $this->chats->getGroupChatId($chatName);
        }

        $messages = [
            'current_chat' => $chatId,
            'type' => $type,
            'messages' => $this->messages->getMessages($chatId),
        ];
        echo json_encode($messages);
    }

    // изменить звук уведомлений чата
    public function editNoticeShow()
    {
        $type = $_POST['type'];
        if ($type !== 'personal' && $type !== 'group') {
            exit('ChatController->editNoticeShow: неверный тип группы');
        }

        $chatName = htmlspecialchars($_POST['chat_name']);
        switch ($type) {
            case 'personal':
                $contactId = $this->users->getIdByName($chatName);
                $chatId = $this->chats->getPersonalChatId($this->authUserId, $contactId);
                break;
            case 'group':
                $chatId = $this->chats->getGroupChatId($chatName);
        }

        $notice = htmlspecialchars($_POST['notice']);
        $notice = intval($notice);
        $isEdited = $this->chatParticipants->updateNoticeShow($chatId, $this->authUserId, $notice);
        echo json_encode(['responce' => $isEdited]);
    }

    // добавить нового участника в группу
    public function createGroupContact()
    {
        $chatName = htmlspecialchars($_POST['chat_name']);
        $chatId = $this->chats->getGroupChatId($chatName);

        $username = htmlspecialchars($_POST['username']);
        $userId = $this->users->getIdByName($username);

        $gcExisted = $this->chatParticipants->exists($chatId, $userId);
        if (!$gcExisted) {
            $isAdded = (int) $this->chatParticipants->add($chatId, $userId);
        } else {
            $isAdded = 1;
        }

        echo json_encode([
            'result' => $isAdded,
            'group' => $chatName,
            'user' => $username,
        ]);
    }
}
