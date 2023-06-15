<?php
    class LoginController extends \core\Controller { 
        function action_index() { 
            $this->view->generate('template_view.php', 'login_view.php', 'login.css', 'login.js', 'Месенджер: войти'); 
        } 
    }
?>