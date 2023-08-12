<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы 404 */
class Page404Controller extends Controller
{
    public function index()
    {
        $this->view->generate('template_view.php', 'page404_view.php', '', '', 'Ошибка 404');
    }
}
