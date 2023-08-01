<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка участников группового чата */
class GetGroupContactsController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
