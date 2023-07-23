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
        echo json_encode($this->contactsTable->addGroupContact($_POST['discussionid'], $this->usersTable->getUserId($_POST['username'])));
    }
}
?>
