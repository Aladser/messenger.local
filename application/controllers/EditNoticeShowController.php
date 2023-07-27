<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер изменения звукового уведомления контакта/группового чата */
class EditNoticeShowController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
