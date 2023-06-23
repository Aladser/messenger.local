<?php

class UploadFileModel extends \core\Model
{
    public function run(){
        session_start();
        // почта пользователя
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $ext = explode('.', $_FILES['image']['name'])[1];

        // поиск других загрузок изображений этого профиля и установка нового имени файла
        $dwlfiles = glob("application/data/temp/$email*");
        if(count($dwlfiles) == 0){
            $filename = $email.'.1.'.$ext;
        }
        else{
            $dwlFile = $dwlfiles[count($dwlfiles)-1];
            $dwlfiles = explode('.', $dwlFile);
            $number = intval($dwlfiles[count($dwlfiles)-2]);
            $filename = $email.'.'.++$number.'.'.$ext;
        }

        $fromPath = $_FILES['image']['tmp_name']; // откуда перемещается
        $toPath =  dirname(__DIR__, 1)."\data\\temp\\$filename"; // куда перемещается
        echo move_uploaded_file($fromPath, $toPath) ? $filename : '';
        // удаление предыдущих вариантов изображения
        for($i=1; $i<$number; $i++){
            $filename = dirname(__DIR__, 1)."\data\\temp\\$email.$i.$ext";
            if(file_exists($filename)){ 
                unlink( dirname(__DIR__, 1)."\data\\temp\\$email.$i.$ext");
            }
        }
    }
}