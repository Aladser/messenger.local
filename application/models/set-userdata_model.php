<?php

/** Изменить пользовательские данные */
class SetUserDataModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG){
        $this->users = $CONFIG->getUsers();
    }

    public function run(){
        //удаление старого файла изображения профиля
        session_start();
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : $_COOKIE['email'];
        foreach (glob("application/data/profile_photos/$email*") as $file) unlink($file);

         // перемещение изображения профиля в папку изображений профилей
        $data['user_email'] = $email;
        $data['user_nickname'] = $_POST['user_nickname'];
        $data['user_hide_email'] = $_POST['user_hide_email'];
        $data['user_photo'] = $_POST['user_photo'];
        $frompath = dirname(__DIR__, 1).'\\data\temp\\'.$data['user_photo'];
        $topath = dirname(__DIR__, 1).'\\data\profile_photos\\'.$data['user_photo'];
        if(rename($frompath, $topath)){
            echo $this->users->setUserData($data);
        }
        else{
            echo 0;
        }
    }
}