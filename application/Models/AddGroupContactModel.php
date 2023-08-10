<?php

namespace Aladser\Models;

use Aladser\Core\Model;

// Контакты пользователя
class AddGroupContactModel extends Model
{
    public function addGroupContact($discussionId, $userId)
    {
        return $this->dbCtl->getContacts()->addGroupContact($discussionId, $userId);
    }
}