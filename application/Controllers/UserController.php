<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\EMailSender;
use App\Models\UserEntity;

use function App\config;

/** Контроллер пользователей */
class UserController extends Controller
{
    private UserEntity $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserEntity();
    }

    // станица авторизации
    public function login(): void
    {
        if (isset($_GET['wrong_password'])) {
            $data['error'] = 'Неверный пароль';
        } elseif (isset($_GET['wrong_user'])) {
            $data['error'] = 'Пользователь не существует';
        }
        $data['csrf'] = MainController::createCSRFToken();

        $this->view->generate(
            'Месенджер - войдите в систему',
            'template_view.php',
            'users/login_view.php',
            null,
            null,
            null,
            $data
        );
    }

    // авторизация пользователя
    public function auth(): void
    {
        // декодирование данных
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
                header('Location: /dialogs');
            } else {
                header('Location: /login?wrong_password=true');
            }
        } else {
            header('Location: /login?wrong_user=true');
        }
    }

    // страница регистрации
    public function register(): void
    {
        if (isset($_GET['difpassw'])) {
            $data['error'] = 'Введенные пароли не совпадают';
        } elseif (isset($_GET['shortpass'])) {
            $data['error'] = 'Длина пароля не менее 3 символов';
        } elseif (isset($_GET['user_exists'])) {
            $data['error'] = 'Пользователь существует';
        } elseif (isset($_GET['add_user_error'])) {
            $data['error'] = 'Ошибка регистрации. Попробуйте позже';
        }
        $data['csrf'] = MainController::createCSRFToken();

        $this->view->generate(
            'Месенджер - регистрация нового пользователя',
            'template_view.php',
            'users/register_view.php',
            null,
            null,
            null,
            $data
        );
    }

    // добавить нового пользователя в БД
    public function store(): void
    {
        // проверка ввода паролей
        if ($_POST['password'] !== $_POST['password_confirm']) {
            echo 'Введенные пароли не совпадают';
            header('Location: /register?difpassw=1');

            return;
        }
        // проверка длины пароля
        if (strlen($_POST['password']) < 3) {
            header('Location: /register?shortpass=1');

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
                $_SESSION['auth'] = 1;
                $_SESSION['email'] = $email;
                setcookie('auth', 1, time() + 60 * 60 * 24, '/');
                setcookie('email', $email, time() + 60 * 60 * 24, '/');
                header('Location: /dialogs');
            } else {
                header('Location: /register?add_user_error=1');
            }
        } else {
            header('Location: /register?user_exists=1');
        }

        echo json_encode($data);
    }

    // страница пользователя
    public function profile(): void
    {
        $email = self::getAuthUserEmail();
        $data['csrf'] = MainController::createCSRFToken();
        $data['user-email'] = $email;
        $data['user_nickname'] = $this->users->get($email, 'user_nickname');
        $data['user_hide_email'] = $this->users->get($email, 'user_hide_email');
        $data['user_photo'] = $this->users->get($email, 'user_photo');
        $this->view->generate(
            'Профиль',
            'template_view.php',
            'users/profile_view.php',
            null,
            'profile.css',
            ['ServerRequest.js', 'profile.js'],
            $data
        );
    }

    // обновить пользователя в БД
    public function update(): void
    {
        $email = self::getAuthUserEmail();
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
                $rsltUpdated = ['result' => (int) $this->users->setUserData($data)];
            } else {
                $rsltUpdated = ['result' => 0];
            }
        } else {
            $data['user_photo'] = $filename;
            $rsltUpdated = ['result' => (int) $this->users->setUserData($data)];
        }
        echo json_encode($rsltUpdated);
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
    public static function getAuthUserEmail()
    {
        if (isset($_COOKIE['email'])) {
            return $_COOKIE['email'];
        } elseif (isset($_SESSION['email'])) {
            return $_SESSION['email'];
        } else {
            return null;
        }
    }

    public function is_nickname_unique()
    {
        $isExisted = $this->users->exists('user_nickname', $_POST['nickname']);
        echo json_encode(['unique' => (int) !$isExisted]);
    }
}
