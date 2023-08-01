<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Добавить контакт(чат) с пользователем */
class ChatsModel extends Model
{
    private $userTable;

    public function __construct($CONFIG)
    {
        $this->userTable = $CONFIG->getUsers();
    }

    public function run()
    {
        $userEmail = Model::getUserMailFromClient();
        $publicUsername = $this->userTable->getPublicUsernameFromEmail($userEmail);
        $userId = $this->userTable->getUserId($userEmail);

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath . $userEmail . '*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $data['csrfToken'] = Model::createCSRFToken();
        return $data;
    }
}
