<?php

namespace Aladser\Models;

use Aladser\Core\Model;

// Данные контакта-пользователя
class GetContactModel extends Model
{
    public function existsContact($contactId, $userId)
    {
        return $this->dbCtl->getContacts()->existsContact($contactId, $userId);
    }

    public function addContact($contactId, $userId)
    {
        return $this->dbCtl->getContacts()->addContact($contactId, $userId);
    }

    public function getDialogId($userId, $contactId)
    {
        return $this->dbCtl->getMessageDBTable()->getDialogId($userId, $contactId);
    }

    public function getContact($userId, $contactId)
    {
        return $this->dbCtl->getContact($userId, $contactId);
    }
}
