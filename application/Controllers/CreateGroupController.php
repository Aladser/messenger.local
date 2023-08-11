<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер добавления нового участника группвого чата  */
class CreateGroupController extends Controller
{
    public function actionIndex()
    {
        $userEmail = Controller::getUserMailFromClient();
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);
        $data = $this->dbCtl->getMessageDBTable()->createDiscussion($userId);
        echo json_encode($data);
    }
}
