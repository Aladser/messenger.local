<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер страницы авторизации */
class LoginController extends Controller
{
    public function index()
    {
        $data = ['csrfToken' => Controller::createCSRFToken()];
        $this->view->generate('template_view.php', 'login_view.php', '', 'login.js', 'Месенджер: войти', $data);
    }
}
