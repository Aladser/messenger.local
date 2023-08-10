<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\Model;

/** контроллер добавления нового участника группвого чата  */
class CreateGroupController extends Controller
{
    public function actionIndex()
    {
        $email = Controller::getUserMailFromClient();
        $userId = $this->model->getUserId($email);
        $data = $this->model->createGroupChat($userId);
        echo json_encode($data);
    }
}
