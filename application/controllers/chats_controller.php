<?php
class ChatsController extends \core\Controller
{
    public function action_index()
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'chats_view.php', 'chats.css', 'chats.js', 'Чаты', $data);
    }
}
