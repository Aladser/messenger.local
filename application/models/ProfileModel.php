<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Данные о профиле текущего пользователя */
class ProfileModel extends Model
{
    private $usersTable;

    public function __construct($CONFIG)
    {
        $this->usersTable = $CONFIG->getUsers();
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        session_start();
        $email = Model::getUserMailFromClient();

        // удаление временных файлов текущего профиля
        $tempDirPath = dirname(__DIR__, 1) . "\data\\temp\\";
        foreach (glob($tempDirPath . $email . '*') as $file) {
            unlink($file);
        }
        $data = $this->usersTable->getUserData($email);
        $data['csrfToken'] = Model::createCSRFToken();
        return $data;
    }
}
