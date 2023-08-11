<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка контактов пользователя */
class GetContactsController extends Controller
{
    public function index($getArgs)
    {
        $userEmail = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);
        echo json_encode($this->dbCtl->getContacts()->getContacts($userId));
    }
}
