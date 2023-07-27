<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** АУТЕНТИФИКАЦИЯ И АВТОРИЗАЦИЯ ПОЛЬЗОВАТЕЛЯ */
class LoginModel extends Model
{
    private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        session_start();
        if (!Model::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            // проверка на подмену адреса
            $data['result'] = 'wrong_url';
        } elseif ($this->users->existsUser($_POST['email'])) {
            // проверка введенныъ данных
            $isValidLogin = $this->users->checkUser($_POST['email'], $_POST['password']) == 1;
            if ($isValidLogin) {
                $_SESSION['auth'] = 1;
                $_SESSION['email'] = $_POST['email'];
                setcookie('auth', 1, time() + 60 * 60 * 24, '/');
                setcookie('email', $_POST['email'], time() + 60 * 60 * 24, '/');
                $data['result'] = 'login_user';
            } else {
                $data['result'] = 'login_user_wrong_password';
            }
        } else {
            $data['result'] = 'not_user_exists';
        }

        echo json_encode($data);
    }
}
