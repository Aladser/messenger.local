<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер главной страницы */
class MainController extends Controller
{
    public function index()
    {
        if (isset($_GET['logout'])) {
            setcookie("email", "", time() - 3600, '/');
            setcookie("auth", "", time() - 3600, '/');
            session_destroy();
        }
        $this->view->generate('template_view.php', 'main_view.php', 'main.css', '', 'Меcсенджер');
    }
}
