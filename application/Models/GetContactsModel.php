<?php

namespace Aladser\Models;

use Aladser\Core\Model;

// Контакты пользователя
class GetContactsModel extends Model
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
        $email = Model::getUserMailFromClient();
        $userId = $this->usersTable->getUserId($email);
        echo json_encode($this->contactsTable->getContacts($userId));
    }
}