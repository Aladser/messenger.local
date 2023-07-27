<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Поиск контактов пользователя */
class MainModel extends Model
{
    public function run()
    {
        if (isset($_GET['logout'])) {
            session_start();
            setcookie("email", "", time() - 3600, '/');
            setcookie("auth", "", time() - 3600, '/');
            session_destroy();
        }
    }
}
