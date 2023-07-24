<?php

namespace Aladser\models;

use Aladser\core\Model;

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
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        echo json_encode($this->users->getUsers($_POST['userphrase'], $email));
    }
}
