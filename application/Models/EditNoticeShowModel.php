<?php

namespace Aladser\Models;

use Aladser\Core\Model;

// Контакты пользователя
class EditNoticeShowModel extends Model
{
    public function setNoticeShow($chatId, $userId, $notice)
    {
        return $this->dbCtl->getMessageDBTable()->setNoticeShow($chatId, $userId, $notice);
    }
}
