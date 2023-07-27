<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Проверка уникальности никнейма */
class IsUniqueNicknameModel extends Model
{
    private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        echo $this->users->isUniqueNickname($_POST['nickname']) ? 1 : 0;
    }
}
