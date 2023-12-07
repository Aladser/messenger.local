<?php

namespace Aladser\Controllers;

use Aladser\Core\Config;
use Aladser\Core\Controller;
use Aladser\Models\ContactEntity;
use Aladser\Models\MessageEntity;
use Aladser\Models\UserEntity;

/** контроллер контактов */
class ContactController extends Controller
{
    private ContactEntity $contacts;
    private MessageEntity $messages;
    private UserEntity $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserEntity();
        $this->contacts = new ContactEntity();
        $this->messages = new MessageEntity();
    }

    public function createGroupContact()
    {
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $username = htmlspecialchars($_POST['username']);
        $userId = $this->users->getUserId($username);
        $group = $this->contacts->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }

    public function getGroupContacts()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }
        $discussionId = htmlspecialchars($_POST['discussionid']);
        $creatorId = $this->messages->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->contacts->getGroupContacts($discussionId),
            'creatorName' => $this->users->getPublicUsername($creatorId),
        ]);
    }

    public function getContact()
    {
        $userHostName = Config::getEmailFromClient();
        $userId = $this->users->getUserId($userHostName);
        $contact = htmlspecialchars($_POST['contact']);
        $contactId = $this->users->getUserId($contact);

        // добавляется контакт, если не существует
        $isContact = $this->contacts->existsContact($contactId, $userId);
        if (!$isContact) {
            $this->contacts->addContact($contactId, $userId);
            $chatId = $this->messages->getDialogId($userId, $contactId);
            $contactName = $this->users->getPublicUsername($contactId);
            $userData = ['username' => $contactName, 'chat_id' => $chatId, 'isnotice' => 0];
        } else {
            $contact = $this->contacts->getContact($userId, $contactId);
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
        $userEmail = Config::getEmailFromClient();
        $userId = $this->users->getUserId($userEmail);
        echo json_encode($this->contacts->getContacts($userId));
    }

    public function findContacts()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }
        $userphrase = htmlspecialchars($_POST['userphrase']);
        echo json_encode($this->users->getUsers($userphrase, Config::getEmailFromClient()));
    }

    public function removeContact()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }

        $type = htmlspecialchars($_POST['type']);
        $name = htmlspecialchars($_POST['name']);
        if ($type === 'group') {
            $chatId = $this->messages->getDiscussionId($name);
        } else {
            $clientName = htmlspecialchars($_POST['clientName']);
            $clientId = $this->users->getUserId($clientName);

            $contactId = $this->users->getUserId($name);
            $chatId = $this->messages->getDialogId($clientId, $contactId);
            $this->contacts->removeContact($clientId, $contactId);
        }
        echo json_encode(['response' => $this->messages->removeChat($chatId)]);
    }
}
