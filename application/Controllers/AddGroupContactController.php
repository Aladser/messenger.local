<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\Model;

/** контроллер создания группового чата */
class AddGroupContactController extends Controller
{
    public function actionIndex()
    {
        $discussionId = $_POST['discussionid'];
        $userId = $this->model->getUserId($_POST['username']);
        $group = $this->model->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }
}
