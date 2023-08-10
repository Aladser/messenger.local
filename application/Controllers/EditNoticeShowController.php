<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\Model;

/** контроллер изменения звукового уведомления контакта/группового чата */
class EditNoticeShowController extends Controller
{
    public function actionIndex()
    {
        $userId = $this->model->getUserId($_POST["username"]);
        $notice = intval($_POST["notice"]);
        echo $this->model->setNoticeShow($_POST["chat_id"], $userId, $notice);
    }
}
