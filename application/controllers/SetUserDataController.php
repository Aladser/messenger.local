<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер изменения данных пользователя */
class SetUserDataController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
