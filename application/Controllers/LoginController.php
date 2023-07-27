<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\Model;
use Exception;

/** контрллер страницы авторизации */
class LoginController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionIndex()
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
