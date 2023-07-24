<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class ProfileController extends Controller
{
    public function action_index()
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'profile_view.php', 'profile.css', 'profile.js', 'Профиль', $data);
    }
}
