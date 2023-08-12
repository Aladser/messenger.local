<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер контактов */
class ContactController extends Controller
{
    public function index()
    {
        switch ($_GET['action']) {
            case 'create':
                $this->createGroupContact();
                break;
            case 'get-contact':
                $this->getContact();
                break;
            case 'show':
                $this->getContacts();
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
}
