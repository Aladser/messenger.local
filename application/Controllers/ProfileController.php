<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер страницы профиля */
class ProfileController extends Controller
{
    public function actionIndex()
    {
        $data = $this->model->run();
        $this->view->generate(
            'template_view.php',
            'profile_view.php',
            'profile.css',
            'profile.js',
            'Профиль',
            $data
        );
    }
}
