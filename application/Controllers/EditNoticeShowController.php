<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер изменения звукового уведомления контакта/группового чата */
class EditNoticeShowController extends Controller
{
    public function actionIndex()
    {
        $userId = $this->dbCtl->getUsers()->getUserId($_POST["username"]);
        $notice = intval($_POST["notice"]);
        echo $this->dbCtl->getMessageDBTable()->setNoticeShow($_POST["chat_id"], $userId, $notice);
    }
}
