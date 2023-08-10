<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;

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
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            // проверка на подмену адреса
            echo 'Подмена URL-адреса';
        } elseif ($this->users->existsUser($_POST['email'])) {
            // проверка введенныъ данных
            $isValidLogin = $this->users->checkUser($_POST['email'], $_POST['password']) == 1;
            if ($isValidLogin) {
                $_SESSION['auth'] = 1;
                $_SESSION['email'] = $_POST['email'];
                setcookie('auth', 1, time() + 60 * 60 * 24, '/');
                setcookie('email', $_POST['email'], time() + 60 * 60 * 24, '/');
                echo json_encode(['result' => 1]);
            } else {
                echo 'Неправильный пароль';
            }
        } else {
            echo 'Пользователь не существует';
        }
    }
}
