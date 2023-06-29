<?php
    class BackController extends \core\Controller { 
        function action_index() {
            $data = $this->model->run(); 
        } 
    }
?>