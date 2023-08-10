<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;

/** Поиск контактов пользователя */
class FindContactsModel extends Model
{
    private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        echo json_encode($this->users->getUsers($_POST['userphrase'], Controller::getUserMailFromClient()));
    }
}
