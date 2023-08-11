<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка участников группового чата */
class GetGroupContactsController extends Controller
{
    public function actionIndex()
    {
        $discussionId = $_POST['discussionid'];
        $creatorId = $this->dbCtl->getMessageDBTable()->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->dbCtl->getContacts()->getGroupContacts($discussionId),
            'creatorName' => $this->dbCtl->getUsers()->getPublicUsername($creatorId)
        ]);
    }
}
