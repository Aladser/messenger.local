<?php

namespace Aladser\Core;


use Exception;
use Aladser\Core\DB\DBCtl;

class Model
{
    protected DBCtl $dbCtl;

    public function __construct(DBCtl $dbCtl)
    {
        $this->dbCtl = $dbCtl;
    }

    public function getUserId($userEmail)
    {
        return $this->dbCtl->getUsers()->getUserId($userEmail);
    }

    public function getPublicName(string $userEmail)
    {
        return $this->dbCtl->getUsers()->getPublicUsernameFromEmail($userEmail);
    }

    public function getPublicNameFromID(int $id)
    {
        return $this->dbCtl->getUsers()->getPublicUsername($id);
    }
}
