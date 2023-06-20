<?php
    class ProfileController extends \core\Controller { 
        function action_index() { 
            $this->view->generate('template_view.php', 'profile_view.php', 'profile.css', 'profile.js','Профиль'); 
        } 
    }
?>