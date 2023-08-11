<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка групповых чатов пользователя */
class GetGroupsController extends Controller
{
    public function actionIndex()
    {
        $username = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($username);
        echo json_encode($this->dbCtl->getMessageDBTable()->getDiscussions($userId));
    }
}
