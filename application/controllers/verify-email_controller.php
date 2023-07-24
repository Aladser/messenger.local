<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class VerifyEmailController extends Controller
{
    public function action_index($data = null)
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'verify-email_view.php', '', '', 'Подтверждение почты', $data);
    }
}
