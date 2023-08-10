<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;

/** Поиск контактов пользователя */
class FindContactsModel extends Model
{
    public function getUsers($userphrase)
    {
        return $this->dbCtl->getUsers()->getUsers($userphrase, Controller::getUserMailFromClient());
    }
}
