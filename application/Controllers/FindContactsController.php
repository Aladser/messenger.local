<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер поиска контактов */
class FindContactsController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
