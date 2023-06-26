<?php
    class GetContactsController extends \core\Controller { 
        function action_index() {
            $this->model->run();
        } 
    }
?>