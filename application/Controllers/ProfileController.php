<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы профиля */
class ProfileController extends Controller
{
    public function index()
    {
        $email = Controller::getUserMailFromClient();
        $data = $this->dbCtl->getUsers()->getUserData($email);
        $data['csrfToken'] = Controller::createCSRFToken();
        $this->view->generate('template_view.php', 'profile_view.php', 'profile.css', 'profile.js', 'Профиль', $data);
    }
}
