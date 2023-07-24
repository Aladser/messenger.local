<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class LoginController extends Controller
{
    public function action_index()
    {
        $this->view->generate('template_view.php', 'login_view.php', '', 'login.js', 'Месенджер: войти');
    }
}
