<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер страницы регистрации */
class RegController extends Controller
{
    public function index()
    {
        $data = ['csrfToken' => Controller::createCSRFToken()];
        $this->view->generate('template_view.php', 'reg_view.php', 'reg.css', 'reg.js', 'Месенджер: регистрация', $data);
    }
}
