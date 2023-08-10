<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;

/** Получить группы пользователя */
class GetGroupsModel extends Model
{
    private $userTable;
    private $messageTable;

    public function __construct($CONFIG)
    {
        $this->userTable = $CONFIG->getUsers();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    public function run()
    {
        $username = Controller::getUserMailFromClient();
        $userId = $this->userTable->getUserId($username);
        echo json_encode($this->messageTable->getDiscussions($userId));
    }
}
