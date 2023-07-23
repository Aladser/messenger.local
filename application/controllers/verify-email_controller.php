<?php

class VerifyEmailController extends \core\Controller
{
    public function action_index($data=null)
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'verify-email_view.php', '', '', 'Подтверждение почты', $data);
    }
}
