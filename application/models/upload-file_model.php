<?php

namespace Aladser\Models;

use Aladser\Core\Model;

/** Загрузка временного изображения на сервер  */
class UploadFileModel extends Model
{
    public function run()
    {
        session_start();

        // проверка на подмену адреса
        if (!Model::checkCSRF($_POST['CSRF'], $_SESSION['CSRF'])) {
            echo json_encode(['wrong_url' => 1]);
            return;
        };

        // почта пользователя
        $email = Model::getUserMailFromClient();
        $ext = explode('.', $_FILES['image']['name'])[1];

        // поиск других загрузок изображений этого профиля и установка нового имени файла
        $dwlDirPath = dirname(__DIR__, 1)."\data\\temp\\"; // папка, куда перемещается изображение из $_POST
        $dwlFiles = glob($dwlDirPath.$email.'*'); // поиск файлов по шаблону
        $number = false;
        if (count($dwlFiles) == 0) {
            $filename = $email.'.1.'.$ext;
        } else {
            $dwlFile = $dwlFiles[count($dwlFiles)-1];           // последний файл
            $dwlFiles = explode('.', $dwlFile);                 // имя разбивается по точкам в массив
            $number = intval($dwlFiles[count($dwlFiles)-2]);    // предпоследний индекс файла
            $filename = $email.'.'.++$number.'.'.$ext;
        }

        
        $fromPath = $_FILES['image']['tmp_name']; // откуда перемещается
        $toPath =  "$dwlDirPath$filename"; // куда перемещается

        // перемещение изображения в папку профилей
        $isMoving = move_uploaded_file($fromPath, $toPath);
        if ($isMoving) {
            $data['result'] = 'ok';
            $data['image'] = $filename;
        } else {
            $data['result'] = 'moving_error';
        }
        echo json_encode($data);

        // удаление предыдущих вариантов изображения
        if ($number) {
            for ($i=1; $i<$number; $i++) {
                $filename = "$dwlDirPath$email.$i.$ext";
                if (file_exists($filename)) {
                    unlink("$dwlDirPath$email.$i.$ext");
                }
            }
        }
    }
}
