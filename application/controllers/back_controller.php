<?php
    class BackController extends \core\Controller { 
        function action_index() {
            $this->model->run();
        } 
    }
?>