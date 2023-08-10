<?php

namespace Aladser\Models;

use Aladser\Core\Model;

// Контакты пользователя
class GetContactsModel extends Model
{
    public function getContacts($userId)
    {
        return $this->dbCtl->getContacts()->getContacts($userId);
    }
}
