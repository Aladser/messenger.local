<?php

/** Изменить пользовательские данные */
class SetUserDataModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run(){

        session_start();
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : $_COOKIE['email'];
        $data['user_email'] = $email;
        $data['user_nickname'] = $_POST['user_nickname'];
        $data['user_hide_email'] = $_POST['user_hide_email'];

         // перемещение изображения профиля из временой папки в папку изображений профилей
        $tempDirPath = dirname(__DIR__, 1).'\\data\temp\\';
        $dwlDirPath = dirname(__DIR__, 1).'\\data\profile_photos\\';

        $filename = $_POST['user_photo'];
        $frompath = "$tempDirPath$filename";
        $topath = "$dwlDirPath$filename";
        
        // если загружено новое изображение
        if(file_exists($frompath)){
            foreach (glob("$dwlDirPath$email*") as $file) unlink($file); // удаление старых файлов профиля
            if(rename($frompath, $topath)){
                $data['user_photo'] = $filename;
                echo $this->users->setUserData($data) ? 1 : 0;
            }
            else{
                echo 0;
            }
        }
        else{
            $data['user_photo'] = $filename;
            echo $this->users->setUserData($data) ? 1 : 0;
        }

        foreach(glob("$tempDirPath$email*") as $file) unlink($file);// удаление временных файлов профиля
    }
}
?>
