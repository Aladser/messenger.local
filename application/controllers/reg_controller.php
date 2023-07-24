<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class RegController extends Controller
{
    public function action_index()
    {
        $this->view->generate('template_view.php', 'reg_view.php', 'reg.css', 'reg.js', 'Месенджер: регистрация');
    }
}
