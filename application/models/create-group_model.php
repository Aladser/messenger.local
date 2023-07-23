<?php
/** Создать групповой чат */
class CreateGroupModel extends \core\Model
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
        session_start();
        $username = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $userId = $this->userTable->getUserId($username);
        echo json_encode($this->messageTable->createDiscussion($userId));
    }
}
