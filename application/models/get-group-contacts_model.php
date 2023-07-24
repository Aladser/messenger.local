<?php

namespace Aladser\models;

use Aladser\core\Model;

/** контакты группового чата */
class GetGroupContactsModel extends Model
{
    private $contactsTable;
    private $usersTable;
    private $messagesTable;

    public function __construct($CONFIG)
    {
        $this->contactsTable = $CONFIG->getContacts();
        $this->usersTable = $CONFIG->getUsers();
        $this->messagesTable = $CONFIG->getMessageDBTable();
    }

    public function run()
    {
        $discussionId = $_POST['discussionid'];
        $creatorId = $this->messagesTable->getDiscussionCreatorId($discussionId);
        echo json_encode([
            'participants' => $this->contactsTable->getGroupContacts($discussionId),
            'creatorName' => $this->usersTable->getPublicUsername($creatorId)
        ]);
    }
}
