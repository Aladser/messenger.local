<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Загрузка временного изображения на сервер  */
class UploadFileModel extends Model
{
    public function run()
    {
        $infoMsg = 'Размер файла превышает ' . ini_get('upload_max_filesize') . 'б';
        // проверка размера файла
        if (!array_key_exists('image', $_FILES)) {
            echo $infoMsg;
            return;
        }

        // почта пользователя
        $email = Model::getUserMailFromClient();
        $ext = explode('.', $_FILES['image']['name'])[1];

        // поиск других загрузок изображений этого профиля и установка нового имени файла

        // папка, куда перемещается изображение из $_POST
        $dwlDirPath = dirname(__DIR__, 1)
            . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR
            . 'temp' . DIRECTORY_SEPARATOR;
        $dwlFiles = glob($dwlDirPath . $email . '*'); // поиск файлов по шаблону
        $number = false;
        if (count($dwlFiles) == 0) {
            $filename =  "$email.1.$ext";
        } else {
            $dwlFile = $dwlFiles[count($dwlFiles) - 1];           // последний файл
            $dwlFiles = explode('.', $dwlFile);                 // имя разбивается по точкам в массив
            $number = intval($dwlFiles[count($dwlFiles) - 2]);    // предпоследний индекс файла
            $filename = $email . '.' . ++$number . '.' . $ext;
        }

        $fromPath = $_FILES['image']['tmp_name']; // откуда перемещается
        $toPath = $dwlDirPath.$filename; // куда перемещается

        // перемещение изображения в папку профилей
        //echo ini_get('upload_max_filesize');
        //echo ini_get('post_max_size');
        echo move_uploaded_file($fromPath, $toPath) ? json_encode(['image' => $filename]) : $infoMsg;

        // удаление предыдущих вариантов изображения
        if ($number) {
            for ($i = 1; $i < $number; $i++) {
                $filename = "$dwlDirPath$email.$i.$ext";
                if (file_exists($filename)) {
                    unlink("$dwlDirPath$email.$i.$ext");
                }
            }
        }
    }
}
