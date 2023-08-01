<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы подтверждения почты */
class VerifyEmailController extends Controller
{
    public function actionIndex()
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'verify-email_view.php', '', '', 'Подтверждение почты', $data);
    }
}
