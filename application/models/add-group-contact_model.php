<?php

namespace Aladser\models;

use Aladser\core\Model;

// Контакты пользователя
class AddGroupContactModel extends Model
{
    private $contactsTable;
    private $usersTable;

    public function __construct($CONFIG)
    {
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run()
    {
        $discussionId = $_POST['discussionid'];
        $userId = $this->usersTable->getUserId($_POST['username']);
        $group = $this->contactsTable->addGroupContact($discussionId, $userId);
        echo json_encode($group);
    }
}
