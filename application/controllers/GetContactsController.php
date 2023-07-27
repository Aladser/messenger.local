<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер получения списка контактов пользователя */
class GetContactsController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
