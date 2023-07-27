<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер создания группового чата */
class AddGroupContactController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
