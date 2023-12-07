<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер главной страницы */
class MainController extends Controller
{
    // индексная страница
    public function index()
    {
        if (isset($_GET['logout'])) {
            setcookie('email', '', time() - 3600, '/');
            setcookie('auth', '', time() - 3600, '/');
            session_destroy();
        }
        $this->view->generate('template_view.php', 'main_view.php', 'main.css', '', 'Меcсенджер');
    }

    // 404 страница
    public function error404()
    {
        $this->view->generate('template_view.php', 'page404_view.php', '', '', 'Ошибка 404');
    }
}
