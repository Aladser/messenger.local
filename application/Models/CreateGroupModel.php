<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Создать групповой чат */
class CreateGroupModel extends Model
{
    private $userTable;
    private $messageTable;

    public function __construct($CONFIG)
    {
        $this->userTable = $CONFIG->getUsers();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $userId = $this->userTable->getUserId(Model::getUserMailFromClient());
        $data = $this->messageTable->createDiscussion($userId);
        echo json_encode($data);
    }
}
