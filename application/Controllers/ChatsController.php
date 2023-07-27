<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы чатов */
class ChatsController extends Controller
{
    public function actionIndex()
    {
        $data = $this->model->run();
        $this->view->generate('template_view.php', 'chats_view.php', 'chats.css', 'chats.js', 'Чаты', $data);
    }
}
