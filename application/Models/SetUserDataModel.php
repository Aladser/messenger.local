<?php

namespace Aladser\Models;

use Aladser\Core\Model;
use Aladser\Core\Controller;

/** Изменить пользовательские данные */
class SetUserDataModel extends Model
{
    private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        // проверка на подмену адреса
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo 'подделка URL-адреса';
            return;
        };

        $email = Controller::getUserMailFromClient();
        $data['user_email'] = $email;
        $_POST['user_nickname'] = trim($_POST['user_nickname']);
        $data['user_nickname'] = $_POST['user_nickname'] == '' ? null : $_POST['user_nickname'];
        $data['user_hide_email'] = $_POST['user_hide_email'];

        // перемещение изображения профиля из временой папки в папку изображений профилей
        $tempDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $dwlDirPath = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profile_photos'.DIRECTORY_SEPARATOR;

        $filename = $_POST['user_photo'];
        // вырезает название файла
        $filename = mb_substr($filename, 0, mb_strripos($filename, '?'));

        $fromPath = $tempDirPath . $filename;
        $toPath = $dwlDirPath . $filename;

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
