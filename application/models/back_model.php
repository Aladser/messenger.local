<?php

//***** Выход пользователя из системы *****/
class BackModel extends \core\Model
{
    public function run(){
        $tempDirPath = dirname(__DIR__, 1).'\\data\temp\\';
        foreach(glob("$tempDirPath$email*") as $file) unlink($file);// удаление временных файлов профиля
        header('Location: /chats');
    }
}