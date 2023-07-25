<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\Model;
use Exception;

class LoginController extends Controller
{
    /**
     * @throws Exception
     */
    public function action_index()
    {
        if (isset($_POST['CSRF'])) {
            // проверка аутентификации
            $this->model->run();
        } else {
            // открыть станицу входа
            $this->view->generate(
                'template_view.php',
                'login_view.php',
                '',
                'login.js',
                'Месенджер: войти',
                ['csrfToken' => Model::createCSRFToken()]
            );
        }
    }
}
