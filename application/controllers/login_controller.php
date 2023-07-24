<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class LoginController extends Controller
{
    public function action_index()
    {
        $this->view->generate('template_view.php', 'login_view.php', '', 'login.js', 'Месенджер: войти');
    }
}
