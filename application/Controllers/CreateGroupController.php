<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер добавления нового участника группвого чата  */
class CreateGroupController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
