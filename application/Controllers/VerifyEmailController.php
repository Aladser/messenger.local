<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер страницы подтверждения почты */
class VerifyEmailController extends Controller
{
    public function index()
    {
        $email = htmlspecialchars(str_replace('\'', '', $_GET['email']));
        $hash = htmlspecialchars(str_replace('\'', '', $_GET['hash']));

        if ($this->dbCtl->getUsers()->checkUserHash($email, $hash)) {
            $this->dbCtl->getUsers()->confirmEmail($email);
            $data = 'Электронная почта подтверждена';
        } else {
            $data = 'Ссылка недействительная или некорректная';
        }

        $this->view->generate('template_view.php', 'verify-email_view.php', '', '', 'Подтверждение почты', $data);
    }
}
