<?php

// Контакты пользователя
class AddGroupContactModel extends \core\Model
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
        $rslt = $this->contactsTable->addGroupContact($discussionId, $userId);
        echo json_encode($rslt);
    }
}
