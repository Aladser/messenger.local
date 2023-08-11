<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер страницы авторизации */
class LoginController extends Controller
{
    public function actionIndex()
    {
        if (isset($_POST['CSRF'])) {
            // проверка аутентификации
            if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
                // проверка на подмену адреса
                echo 'Подмена URL-адреса';
            } elseif ($this->dbCtl->getUsers()->existsUser($_POST['email'])) {
                // проверка введенныъ данных
                $isValidLogin = $this->dbCtl->getUsers()->checkUser($_POST['email'], $_POST['password']) == 1;
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
        } else {
            // открыть станицу входа
            $data = ['csrfToken' => Controller::createCSRFToken()];
            $this->view->generate('template_view.php', 'login_view.php', '', 'login.js', 'Месенджер: войти', $data);
        }
    }
}
