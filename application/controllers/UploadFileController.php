<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер загрузки временного изображения профиля */
class UploadFileController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
