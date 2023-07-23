<?php

// Контакты пользователя
class EditNoticeShowModel extends \core\Model
{
    private $usersTable;
    private $messageTable;

    public function __construct($CONFIG)
    {
        $this->usersTable = $CONFIG->getUsers();
        $this->messageTable = $CONFIG->getMessageDBTable();
    }

    public function run()
    {
        $userId = $this->usersTable->getUserId($_POST["username"]);
        $notice = intval($_POST["notice"]);
        echo $this->messageTable->setNoticeShow($_POST["chat_id"], $userId, $notice);
    }
}
?>
