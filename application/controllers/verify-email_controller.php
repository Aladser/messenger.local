<?php
    class VerifyEmailController extends Controller {
        function action_index($data=null) {
            $data = $this->model->getData(); 
            $this->view->generate('template_view.php', 'verify-email_view.php', '', 'verify-email.js', 'Подтверждение почты', $data); 
        } 
    }