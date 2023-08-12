<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер контактов */
class ContactController extends Controller
{
    public function index()
    {
        switch ($_GET['action']) {
            case 'create-group-contact':
                $this->createGroupContact();
                break;
            case 'show-group-contacts':
                $this->getGroupContacts();
                break;
            case 'get-contact':
                $this->getContact();
                break;
            case 'show-contacts':
                $this->getContacts();
                break;
            case 'find-contacts':
                $this->findContacts();
                break;
            case 'remove':
                $this->removeContact();
                break;             
        }
    }
    public function createGroupContact()
    {
        $discussionId = $_POST['discussionid'];
        $userId = $this->dbCtl->getUsers()->getUserId($_POST['username']);
        $group = $this->dbCtl->getContacts()->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }


    public function getGroupContacts()
    {
        $discussionId = $_POST['discussionid'];
        $creatorId = $this->dbCtl->getMessageDBTable()->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->dbCtl->getContacts()->getGroupContacts($discussionId),
            'creatorName' => $this->dbCtl->getUsers()->getPublicUsername($creatorId)
        ]);
    }

    public function getContact()
    {
        $userHostName = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($userHostName);
        $contactId = $this->dbCtl->getUsers()->getUserId($_POST['contact']);

        // добавляется контакт, если не существует
        $isContact = $this->dbCtl->getContacts()->existsContact($contactId, $userId);
        if (!$isContact) {
            $this->dbCtl->getContacts()->addContact($contactId, $userId);
            $chatId = $this->dbCtl->getMessageDBTable()->getDialogId($userId, $contactId);
            $contactName = $this->dbCtl->getUsers()->getPublicUsername($contactId);
            $userData = ['username' => $contactName, 'chat_id' => $chatId, 'isnotice' => 0];
        } else {
            $contact = $this->model->getContact($userId, $contactId);
            $userData = [
                'username' => $contact[0]['username'],
                'chat_id' => $contact[0]['chat_id'],
                'isnotice' => $contact[0]['isnotice']
            ];
        }
        echo json_encode($userData);
    }

    public function getContacts()
    {
        $userEmail = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);
        echo json_encode($this->dbCtl->getContacts()->getContacts($userId));
    }

    public function findContacts()
    {
        echo json_encode($this->dbCtl->getUsers()->getUsers($_POST['userphrase'], Controller::getUserMailFromClient()));
    }

    public function removeContact()
    {
        if ($_POST['type'] === 'group') {
            $chatId = $this->dbCtl->getMessageDBTable()->getDiscussionId($_POST['name']);
        } else {
            $clientId = $this->dbCtl->getUsers()->getUserId($_POST['clientName']);
            $contactId = $this->dbCtl->getUsers()->getUserId($_POST['name']);
            $chatId = $this->dbCtl->getMessageDBTable()->getDialogId($clientId, $contactId);
            
            $this->dbCtl->getContacts()->removeContact($clientId, $contactId);
        }
        echo $this->dbCtl->getMessageDBTable()->removeChat($chatId);
    }
}
