<?php
class MainController extends \core\Controller
{
    public function action_index()
    {
        $this->model->run();
        $this->view->generate('template_view.php', 'main_view.php', 'main.css', '', 'Месенджер');
    }
}
