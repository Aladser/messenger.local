<?php

class UploadFileModel extends \core\Model
{
    public function run(){
        session_start();
        // перемещение временного файла на диск
        $email = isset($_COOKIE['auth']) ?  $_COOKIE['email'] : $_SESSION['email'];
        $ext = explode('.', $_FILES['image']['name'])[1];
        $filename = "$email.$ext";
        $fromPath = $_FILES['image']['tmp_name'];
        $toPath =  dirname(__DIR__, 1)."\data\\temp\\$filename";
        echo move_uploaded_file($fromPath, $toPath) ? "application/data/temp/$filename" : 0;
    }
}