<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;
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
        $email = Controller::getUserMailFromClient();
        $data = $this->usersTable->getUserData($email);
        $data['csrfToken'] = Controller::createCSRFToken();
        return $data;
    }
}
