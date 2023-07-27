<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка групповых чатов пользователя */
class GetGroupsController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
