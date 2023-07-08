<?php
    class ChatsController extends \core\Controller { 
        function action_index() {
            $this->view->generate('template_view.php', 'chats_view.php', 'chats.css', 'chats.js','Чаты', ['usermail' => $this->model->getUserMail()]); 
        } 
    }
?>