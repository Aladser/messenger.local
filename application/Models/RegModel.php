<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\EMailSender;
use Aladser\Core\ConfigClass;

/***** Класс регистрации пользователя ****/
class RegModel extends Model
{
    private $userTable;
    private $eMailSender;

    public function __construct($CONFIG)
    {
        $this->userTable = $CONFIG->getUsers();
        $this->eMailSender = new EMailSender(
            ConfigClass::SMTP_SRV,
            ConfigClass::EMAIL_USERNAME,
            ConfigClass::EMAIL_PASSWORD,
            ConfigClass::SMTP_SECURE,
            ConfigClass::SMTP_PORT,
            ConfigClass::EMAIL_SENDER,
            ConfigClass::EMAIL_SENDER_NAME
        );
    }

    public function run()
    {
        if (!$this->userTable->existsUser($_POST['email'])) {
            // экранирование символов логина и пароля
            //$email = htmlspecialchars($_POST['email']);
            //$password = htmlspecialchars($_POST['password']);
            $email = $_POST['email'];
            $password = $_POST['password'];

            $isRegUser = $this->userTable->addUser($email, $password) === 1;
            if ($isRegUser) {
                $hash = md5($email . time());
                $this->userTable->addUserHash($email, $hash);
                $text = "
                <body>
                <p>Для подтверждения учетной записи в Месенджере перейдите по 
                <a href=\"http://messenger.local/verify-email?email='$email'&hash='$hash'\">ссылке</a>
                </p>
                </body>
                ";
                $data['result'] = $this->eMailSender->send('Месенджер: подтвердите e-mail', $text, $email);
            } else {
                $data['result'] = 'add_user_error';
            }
        } else {
            $data['result'] = 'user_exists';
        }

        echo json_encode($data);
    }
}
