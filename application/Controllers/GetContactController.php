<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения информации о контакте */
class GetContactController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
