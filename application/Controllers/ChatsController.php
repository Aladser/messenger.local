<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы чатов */
class ChatsController extends Controller
{
    public function index()
    {
        $userEmail = Controller::getUserMailFromClient();
        $publicUsername = $this->dbCtl->getUsers()->getPublicUsernameFromEmail($userEmail);
        $userId = $this->dbCtl->getUsers()->getUserId($userEmail);

        // удаление временных файлов профиля
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        foreach (glob($tempDirPath . $userEmail . '*') as $file) {
            unlink($file);
        }

        $data['user-email'] = $userEmail;
        $data['publicUsername'] = $publicUsername;
        $data['userhostId'] = $userId;
        $data['csrfToken'] = Controller::createCSRFToken();
        $this->view->generate('template_view.php', 'chats_view.php', 'chats.css', 'chats.js', 'Чаты', $data);
    }
}
