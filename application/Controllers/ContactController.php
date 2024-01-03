<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ChatEntity;
use App\Models\ContactEntity;
use App\Models\GroupContactEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;

/** контроллер контактов */
class ContactController extends Controller
{
    private ContactEntity $contacts;
    private GroupContactEntity $groupContacts;
    private MessageEntity $messages;
    private UserEntity $users;
    private ChatEntity $chats;

    private string $authUserEmail;
    private int $authUserId;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserEntity();
        $this->contacts = new ContactEntity();
        $this->groupContacts = new GroupContactEntity();
        $this->messages = new MessageEntity();
        $this->authUserEmail = UserController::getAuthUserEmail();
        $this->authUserId = $this->users->getIdByName($this->authUserEmail);
        $this->chats = new ChatEntity();
    }

    public function getContact()
    {
        $contact = htmlspecialchars($_POST['contact']);
        $contactId = $this->users->getIdByName($contact);

        // добавляется контакт, если не существует
        $isContact = $this->contacts->exists($contactId, $this->authUserId);
        if (!$isContact) {
            $this->contacts->add($contactId, $this->authUserId);
            $chatId = $this->messages->getDialogId($this->authUserId, $contactId);
            $contactName = $this->users->getPublicUsername($contactId);
            $userData = ['username' => $contactName, 'chat_id' => $chatId, 'isnotice' => 1];
        } else {
            $contact = $this->contacts->get($this->authUserId, $contactId);
            $userData = [
                'username' => $contact[0]['username'],
                'chat_id' => $contact[0]['chat_id'],
                'isnotice' => $contact[0]['isnotice'],
            ];
        }
        echo json_encode($userData);
    }

    public function getContacts()
    {
        echo json_encode($this->contacts->getUserContacts($this->authUserId));
    }

    public function find()
    {
        $userphrase = htmlspecialchars($_POST['userphrase']);
        echo json_encode($this->users->getUsersByPhrase($userphrase, $this->authUserEmail));
    }

    public function add()
    {
        // id контакта
        $contactName = htmlspecialchars($_POST['username']);
        $contactId = $this->users->getIdByName($contactName);
        // создаем чат
        $chatId = $this->chats->add('dialog', $this->authUserId);
        // создаем участников чата
        $this->contacts->add($chatId, $this->authUserId);
        $this->contacts->add($chatId, $contactId);

        $userData = ['username' => $contactName, 'chat_id' => $chatId, 'isnotice' => 1];
        echo json_encode($userData);
    }

    public function remove()
    {
        $type = htmlspecialchars($_POST['type']);
        $name = htmlspecialchars($_POST['name']);
        if ($type === 'group') {
            $chatId = $this->messages->getDiscussionId($name);
        } else {
            $clientName = htmlspecialchars($_POST['clientName']);
            $clientId = $this->users->getIdByName($clientName);

            $contactId = $this->users->getIdByName($name);
            $chatId = $this->messages->getDialogId($clientId, $contactId);
            $this->contacts->remove($clientId, $contactId);
        }
        echo json_encode(['response' => $this->messages->removeChat($chatId)]);
    }

    public function createGroupContact()
    {
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $username = htmlspecialchars($_POST['username']);
        $userId = $this->users->getIdByName($username);

        $gcExisted = $this->groupContacts->exists($discussionId, $userId);
        if (!$gcExisted) {
            $group = $this->groupContacts->add($discussionId, $userId);
        } else {
            $group = 1;
        }

        echo json_encode([
            'result' => $group,
            'group' => 'group-'.$discussionId,
            'user' => $username,
        ]);
    }

    public function getGroupContacts()
    {
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $creatorId = $this->messages->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->contacts->getGroupContacts($discussionId),
            'creatorName' => $this->users->getPublicUsername($creatorId),
        ]);
    }
}
