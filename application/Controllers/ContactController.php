<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер контактов */
class ContactController extends Controller
{
    public function createGroupContact($getArgs)
    {
        $discussionId = $_POST['discussionid'];
        $userId = $this->dbCtl->getUsers()->getUserId($_POST['username']);
        $group = $this->dbCtl->getContacts()->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }
}
