<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения информации о контакте */
class GetContactController extends Controller
{
    public function index()
    {
        // CSRF-проверка на подмену адреса
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo json_encode(['wrong_url' => 1]);
            exit;
        };

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
}
