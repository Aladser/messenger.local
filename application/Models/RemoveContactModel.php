<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Удаление группового чата */
class RemoveContactModel extends Model
{
    private $messageTable;
    private $userTable;
    private $contactsTable;

    public function __construct($CONFIG)
    {
        $this->messageTable = $CONFIG->getMessageDBTable();
        $this->userTable = $CONFIG->getUsers();
        $this->contactsTable = $CONFIG->getContacts();
    }

    public function run()
    {
        if ($_POST['type'] === 'group') {
            $chatId = $this->messageTable->getDiscussionId($_POST['name']);
        } else {
            $clientId = $this->userTable->getUserId($_POST['clientName']);
            $contactId = $this->userTable->getUserId($_POST['name']);
            $chatId = $this->messageTable->getDialogId($clientId, $contactId);
            
            $this->contactsTable->removeContact($clientId, $contactId);
        }
        echo $this->messageTable->removeChat($chatId);
    }
}
