<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class RegController extends Controller
{
    public function action_index()
    {
        if (isset($_POST['registration'])) {
            $this->model->run();
        } else {
            $this->view->generate(
                'template_view.php',
                'reg_view.php',
                'reg.css',
                'reg.js',
                'Месенджер: регистрация'
            );
        }
    }
}
