<?php
    class Cash_Controller extends Controller { 
        function action_index() { 
            $this->view->generate('template_view.php', 'cash_view.php', 'cash.css', 'Касса'); 
        } 
    }
?>