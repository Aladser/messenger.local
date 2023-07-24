<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class MainController extends Controller
{
    public function action_index()
    {
        $this->model->run();
        $this->view->generate('template_view.php', 'main_view.php', 'main.css', '', 'Месенджер');
    }
}
