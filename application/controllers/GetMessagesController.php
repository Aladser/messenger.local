<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер получения сообщений открытого чата */
class GetMessagesController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
