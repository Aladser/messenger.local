<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Создать групповой чат */
class CreateGroupModel extends Model
{
    public function createGroupChat($userId)
    {
        return $this->dbCtl->getMessageDBTable()->createDiscussion($userId);
    }
}
