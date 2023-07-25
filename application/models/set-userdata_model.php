<?php

namespace Aladser\Models;

use Aladser\Core\Model;

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

        session_start();
        // проверка на подмену адреса
        if (!Model::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo json_encode(['wrong_url' => 1]);
            return;
        };

        $email = Model::getUserMailFromClient();
        $data['user_email'] = $email;
        $_POST['user_nickname'] = trim($_POST['user_nickname']);
        $data['user_nickname'] = $_POST['user_nickname'] == '' ? null :  $_POST['user_nickname'];
        $data['user_hide_email'] = $_POST['user_hide_email'];

        // перемещение изображения профиля из временой папки в папку изображений профилей
        $tempDirPath = dirname(__DIR__, 1).'\\data\temp\\';
        $dwlDirPath = dirname(__DIR__, 1).'\\data\profile_photos\\';

        $filename = $_POST['user_photo'];
        $filename = mb_substr($filename, 0, mb_strripos($filename, '?'));
        $fromPath = $tempDirPath.$filename;
        $toPath = $dwlDirPath.$filename;
        
        // если загружено новое изображение
        if (file_exists($fromPath)) {
            foreach (glob("$dwlDirPath$email*") as $file) {
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

        foreach (glob("$tempDirPath$email*") as $file) {
            unlink($file);// удаление временных файлов профиля
        }
    }
}
