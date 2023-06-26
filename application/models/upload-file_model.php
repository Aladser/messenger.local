<?php

/** Загрузка временного изображения на сервер  */
class UploadFileModel extends \core\Model
{
    public function run(){
        session_start();
        // почта пользователя
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $ext = explode('.', $_FILES['image']['name'])[1];

        // поиск других загрузок изображений этого профиля и установка нового имени файла
        $dwlDirPath = dirname(__DIR__, 1)."\data\\temp\\"; // папка, куда перемещается изображение из $_POST
        $dwlfiles = glob("$dwlDirPath$email*");
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
        $toPath =  "$dwlDirPath$filename"; // куда перемещается
        echo move_uploaded_file($fromPath, $toPath) ? $filename : '';
        // удаление предыдущих вариантов изображения
        for($i=1; $i<$number; $i++){
            $filename = "$dwlDirPath$email.$i.$ext";
            if(file_exists($filename)){ 
                unlink( "$dwlDirPath$email.$i.$ext");
            }
        }
    }
}