<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Exception;

/** Данные о профиле текущего пользователя */
class ProfileModel extends Model
{
    private $usersTable;

    public function __construct($CONFIG)
    {
        $this->usersTable = $CONFIG->getUsers();
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $email = Model::getUserMailFromClient();
        $data = $this->usersTable->getUserData($email);
        $data['csrfToken'] = Model::createCSRFToken();
        return $data;
    }
}
