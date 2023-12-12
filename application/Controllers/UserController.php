<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;
use Aladser\Core\EMailSender;
use Aladser\Models\UserEntity;

use function Aladser\config;

/** Контроллер пользователей */
class UserController extends Controller
{
    private UserEntity $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserEntity();
    }

    public function is_nickname_unique()
    {
        $isExisted = $this->users->exists('user_nickname', $_POST['nickname']);
        echo json_encode(['unique' => (int) !$isExisted]);
    }

    // авторизация пользователя
    public function auth(): void
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }

        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);

        // проверка аутентификации
        if ($this->users->exists('user_email', $email)) {
            // проверка введенных данных
            $isValidLogin = $this->users->verify($email, $password);
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

    // добавить нового пользователя в БД
    public function store(): void
    {
        // проверка CSRF
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'Подмена URL-адреса';

            return;
        }

        $eMailSender = new EMailSender();
        $email = htmlspecialchars($_POST['email']);
        $app_name = config('APP_NAME');
        if (!$this->users->exists('user_email', $email)) {
            $password = htmlspecialchars($_POST['password']);
            $isAdded = $this->users->add($email, $password);
            if ($isAdded) {
                $hash = md5($email.time());
                $this->users->addUserHash($email, $hash);
                $text = "
                <body>
                    <p>Для подтверждения учетной записи в Месенджере перейдите по 
                        <a href=\"http://$app_name/verify-email?email='$email'&hash='$hash'\">ссылке</a>
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

    // обновить пользователя в БД
    public function update(): void
    {
        // проверка на подмену адреса
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            echo 'подделка URL-адреса';

            return;
        }

        $email = self::getEmailFromClient();
        $data['user_email'] = $email;
        $nickname = trim($_POST['user_nickname']);
        $data['user_nickname'] = $nickname == '' ? null : $nickname;
        $data['user_hide_email'] = $_POST['user_hide_email'];

        // перемещение изображения профиля из временой папки в папку изображений профилей
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $downloadDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profile_photos'.DIRECTORY_SEPARATOR;

        $filename = $_POST['user_photo'];
        // вырезает название файла без учета регистра
        $filename = mb_substr($filename, 0, mb_strripos($filename, '?'));

        $fromPath = $tempDirPath.$filename;
        $toPath = $downloadDirPath.$filename;

        // если загружено новое изображение
        if (file_exists($fromPath)) {
            // удаление старых файлов профиля
            foreach (glob($downloadDirPath.$email.'*') as $file) {
                unlink($file);
            }
            // переименование файла
            if (rename($fromPath, $toPath)) {
                $data['user_photo'] = $filename;
                echo (int) $this->users->setUserData($data);
            } else {
                echo 0;
            }
        } else {
            $data['user_photo'] = $filename;
            echo (int) $this->users->setUserData($data);
        }
    }

    // станица авторизации
    public function login(): void
    {
        $data = ['csrfToken' => MainController::createCSRFToken()];
        $this->view->generate(
            'Месенджер - войдите в систему',
            'template_view.php',
            'users/login_view.php',
            null,
            '',
            ['login.js'],
            $data
        );
    }

    // страница регистрации
    public function register(): void
    {
        $data = ['csrfToken' => MainController::createCSRFToken()];
        $this->view->generate(
            'Месенджер - регистрация',
            'template_view.php',
            'users/register_view.php',
            null,
            'reg.css',
            ['reg.js'],
            $data
        );
    }

    // страница пользователя
    public function profile(): void
    {
        // почта
        $email = self::getEmailFromClient();
        // пользователи
        $users = new UserEntity();
        // данные пользователя
        $data = $users->getUserData($email);
        // CSRF
        $data['csrfToken'] = MainController::createCSRFToken();

        $this->view->generate(
            'Профиль',
            'template_view.php',
            'users/profile_view.php',
            null,
            'profile.css',
            ['profile.js'],
            $data
        );
    }

    // подтверждение почты после регистрации
    public function verifyEmail(): void
    {
        $email = htmlspecialchars(str_replace('\'', '', $_GET['email']));
        $hash = htmlspecialchars(str_replace('\'', '', $_GET['hash']));

        if ($this->users->checkUserHash($email, $hash)) {
            $this->users->confirmEmail($email);
            $data = 'Электронная почта подтверждена';
        } else {
            $data = 'Ссылка недействительная или некорректная';
        }

        $this->view->generate(
            'Месенджер - подтверждение почты',
            'template_view.php',
            'verify-email_view.php',
            null,
            '',
            '',
            $data
        );
    }

    /** получить почту пользователя из сессии или куки */
    public static function getEmailFromClient()
    {
        if (isset($_COOKIE['email'])) {
            return $_COOKIE['email'];
        } elseif (isset($_SESSION['email'])) {
            return $_SESSION['email'];
        } else {
            return null;
        }
    }
}
