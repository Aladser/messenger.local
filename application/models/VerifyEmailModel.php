<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Подтверждение почты */
class VerifyEmailModel extends Model
{
    private $usersTable;

    public function __construct($CONFIG)
    {
        $this->usersTable = $CONFIG->getUsers();
    }

    public function run(): string
    {
        // замена спец символов на html-коды и удаление лишних символов
        $email = htmlspecialchars(str_replace('\'', '', $_GET['email']));
        $hash = htmlspecialchars(str_replace('\'', '', $_GET['hash']));

        if ($this->usersTable->checkUserHash($email, $hash)) {
            $this->usersTable->confirmEmail($email);
            return 'Электронная почта подтверждена';
        } else {
            return 'Ссылка недействительная или некорректная';
        }
    }
}
