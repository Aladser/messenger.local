<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\EMailSender;
use Aladser\Core\ConfigClass;

/** контрллер страницы регистрации */
class RegController extends Controller
{
    public function index()
    {
        if (isset($_POST['registration'])) {
            $this->registerUser();
        } else {
            $this->view->generate('template_view.php', 'reg_view.php', 'reg.css', 'reg.js', 'Месенджер: регистрация');
        }
    }

    private function registerUser()
    {
        $eMailSender = new EMailSender(
            ConfigClass::SMTP_SRV,
            ConfigClass::EMAIL_USERNAME,
            ConfigClass::EMAIL_PASSWORD,
            ConfigClass::SMTP_SECURE,
            ConfigClass::SMTP_PORT,
            ConfigClass::EMAIL_SENDER,
            ConfigClass::EMAIL_SENDER_NAME
        );

        if (!$this->dbCtl->getUsers()->existsUser($_POST['email'])) {
            // экранирование символов логина и пароля
            //$email = htmlspecialchars($_POST['email']);
            //$password = htmlspecialchars($_POST['password']);
            $email = $_POST['email'];
            $password = $_POST['password'];

            $isRegUser = $this->dbCtl->getUsers()->addUser($email, $password) === 1;
            if ($isRegUser) {
                $hash = md5($email . time());
                $this->dbCtl->getUsers()->addUserHash($email, $hash);
                $text = "
                <body>
                <p>Для подтверждения учетной записи в Месенджере перейдите по 
                <a href=\"http://messenger.local/verify-email?email='$email'&hash='$hash'\">ссылке</a>
                </p>
                </body>
                ";
                $data['result'] = $eMailSender->send('Месенджер: подтвердите e-mail', $text, $email);
            } else {
                $data['result'] = 'add_user_error';
            }
        } else {
            $data['result'] = 'user_exists';
        }

        echo json_encode($data);
    }
}
