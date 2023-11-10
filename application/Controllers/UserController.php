<?php

namespace Aladser\Controllers;

use Aladser\Core\ConfigClass;
use Aladser\Core\Controller;
use Aladser\Core\DB\DBCtl;
use Aladser\EMailSender;
use Aladser\Models\UsersDBTableModel;

/** контрллер проверки уникальности никнейма */
class UserController extends Controller
{
    private UsersDBTableModel $users;

    public function __construct(DBCtl $dbCtl = null)
    {
        parent::__construct($dbCtl);
        $this->users = $dbCtl->getUsers();
    }

    public function isUniqueNickname()
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }

        $nickname = htmlspecialchars($_POST['nickname']);
        $response = $this->users->isUniqueNickname($nickname) ? 1 : 0;
        echo json_encode(['response' => $response]);
    }

    public function login()
    {
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);
        // проверка аутентификации
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';
        } elseif ($this->users->existsUser($email)) {
            // проверка введенных данных
            $isValidLogin = $this->users->checkUser($email, $password) == 1;
            if ($isValidLogin) {
                $_SESSION['auth'] = 1;
                $_SESSION['email'] = $email;
                setcookie('auth', 1, time() + 60 * 60 * 24, '/');
                setcookie('email', $email, time() + 60 * 60 * 24, '/');
                echo json_encode(['result' => 1]);
            } else {
                echo 'Неправильный пароль';
            }
        } else {
            echo 'Пользователь не существует';
        }
    }

    public function register()
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

        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }

        if (!$this->users->existsUser($_POST['email'])) {
            $email = htmlspecialchars($_POST['email']);
            $password = htmlspecialchars($_POST['password']);
            $email = $email;
            $password = $password;

            $isRegUser = $this->users->addUser($email, $password) === 1;
            if ($isRegUser) {
                $hash = md5($email.time());
                $this->users->addUserHash($email, $hash);
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

    public function update()
    {
        // проверка на подмену адреса
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'подделка URL-адреса';

            return;
        }

        $email = Controller::getUserMailFromClient();
        $data['user_email'] = $email;
        $nickname = trim($_POST['user_nickname']);
        $data['user_nickname'] = $nickname == '' ? null : $nickname;
        $data['user_hide_email'] = $_POST['user_hide_email'];

        // перемещение изображения профиля из временой папки в папку изображений профилей
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $dwlDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profile_photos'.DIRECTORY_SEPARATOR;

        $filename = $_POST['user_photo'];
        // вырезает название файла
        $filename = mb_substr($filename, 0, mb_strripos($filename, '?'));

        $fromPath = $tempDirPath.$filename;
        $toPath = $dwlDirPath.$filename;

        // если загружено новое изображение
        if (file_exists($fromPath)) {
            foreach (glob($dwlDirPath.$email.'*') as $file) {
                unlink($file); // удаление старых файлов профиля
            }
            if (rename($fromPath, $toPath)) {
                $data['user_photo'] = $filename;
                echo $this->users->setUserData($data) ? 1 : 0;
            } else {
                echo 0;
            }
        } else {
            $data['user_photo'] = $filename;
            echo $this->users->setUserData($data) ? 1 : 0;
        }
    }
}
