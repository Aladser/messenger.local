<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка контактов пользователя */
class GetContactsController extends Controller
{
    public function actionIndex()
    {
        $email = Controller::getUserMailFromClient();
        $userId = $this->model->getUserId($email);
        echo json_encode($this->model->getContacts($userId));
    }
}
