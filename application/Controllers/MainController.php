<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер главной страницы */
class MainController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
        $this->view->generate(
            'template_view.php',
            'main_view.php',
            'main.css',
            '',
            'Месенджер'
        );
    }
}
