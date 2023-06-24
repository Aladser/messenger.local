<?php
    class FindContactsController extends \core\Controller { 
        function action_index() {
            $this->model->run();
        } 
    }
?>