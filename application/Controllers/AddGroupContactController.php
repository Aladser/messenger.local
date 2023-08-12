<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер создания группового чата */
class AddGroupContactController extends Controller
{
    public function index()
    {
        $discussionId = $_POST['discussionid'];
        $userId = $this->dbCtl->getUsers()->getUserId($_POST['username']);
        $group = $this->dbCtl->getContacts()->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }
}
