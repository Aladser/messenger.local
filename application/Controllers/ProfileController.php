<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Models\UserEntity;

/** контроллер страницы профиля */
class ProfileController extends Controller
{
    public function index()
    {
        // почта
        $email = Controller::getUserMailFromClient();
        // пользователи
        $users = new UserEntity();
        // данные пользователя
        $data = $users->getUserData($email);
        // CSRF
        $data['csrfToken'] = Controller::createCSRFToken();

        $this->view->generate('template_view.php', 'profile_view.php', 'profile.css', 'profile.js', 'Профиль', $data);
    }
}
