<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class LoginController extends Controller
{
    public function action_index()
    {
        if (array_key_exists('csrf', $_POST)) {
            // проверка аутентификации
            $this->model->run();
        } else {
            // открытие страницы входа
            session_start();

            //****** CSRF добавляется на страницу и в сессию *****
            $data['csrfToken'] = hash('gost-crypto', random_int(0, 999999));
            $_SESSION["CSRF"] = $data['csrfToken'];

            $this->view->generate('template_view.php', 'login_view.php', '', 'login.js', 'Месенджер: войти', $data);
        }
    }
}
