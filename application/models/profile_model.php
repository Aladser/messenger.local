<?php

/** Данные о профиле текущего пользователя */
class ProfileModel extends \core\Model
{
	private $users;

    public function __construct($CONFIG)
    {
        $this->users = $CONFIG->getUsers();
    }

    public function run()
    {
        session_start();
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];

        // удаление временных файлов текущего профиля
        $tempDirPath = dirname(__DIR__, 1)."\data\\temp\\";
        foreach(glob($tempDirPath.$email.'*') as $file) unlink($file);

        return $this->users->getUserData($email);
    }
}


