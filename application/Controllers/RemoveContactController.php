<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер удаления группового чата */
class RemoveContactController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
