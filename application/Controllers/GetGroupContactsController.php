<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка участников группового чата */
class GetGroupContactsController extends Controller
{
    public function actionIndex()
    {
        $discussionId = $_POST['discussionid'];
        $creatorId = $this->model->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->model->getGroupContacts($discussionId),
            'creatorName' => $this->model->getPublicNameFromID($creatorId)
        ]);
    }
}
