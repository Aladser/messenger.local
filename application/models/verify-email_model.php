<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Подтверждение почты */
class VerifyEmailModel extends Model
{
    private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run(): string
    {
        if ($this->users->checkUserHash($_GET['email'], $_GET['hash'])) {
            $this->users->confirmEmail($_GET['email']);
            return 'Электронная почта подтверждена';
        } else {
            return 'Ссылка недействительная или некорректная';
        }
    }
}
