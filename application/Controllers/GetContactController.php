<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения информации о контакте */
class GetContactController extends Controller
{
    public function actionIndex()
    {
        // CSRF-проверка на подмену адреса
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo json_encode(['wrong_url' => 1]);
            exit;
        };

        $userHostName = Controller::getUserMailFromClient();           // имя клиента-хоста
        $userId = $this->model->getUserId($userHostName);         // id клиента-хоста
        $contactId = $this->model->getUserId($_POST['contact']);  // id клиента-контакта

        // добавляется контакт, если не существует
        $isContact = $this->model->existsContact($contactId, $userId);
        if (!$isContact) {
            $this->model->addContact($contactId, $userId);
            $chatId = $this->model->getDialogId($userId, $contactId); // создается диалог, если не существует
            $contactName = $this->model->getPublicNameFromID($contactId);
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
