<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер изменения данных пользователя */
class SetUserDataController extends Controller
{
    public function index($getArgs)
    {
        // проверка на подмену адреса
        if (!Controller::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo 'подделка URL-адреса';
            return;
        };

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

        $fromPath = $tempDirPath . $filename;
        $toPath = $dwlDirPath . $filename;

        // если загружено новое изображение
        if (file_exists($fromPath)) {
            foreach (glob($dwlDirPath.$email.'*') as $file) {
                unlink($file); // удаление старых файлов профиля
            }
            if (rename($fromPath, $toPath)) {
                $data['user_photo'] = $filename;
                echo $this->dbCtl->getUsers()->setUserData($data) ? 1 : 0;
            } else {
                echo 0;
            }
        } else {
            $data['user_photo'] = $filename;
            echo $this->dbCtl->getUsers()->setUserData($data) ? 1 : 0;
        }
    }
}
