<?php
    class VerifyEmail_Controller extends Controller {
        function action_index($data=null) { 
            $this->view->generate('template_view.php', 'verify-email_view.php', '', '', 'Подтверждение почты', $data); 
        } 
    }