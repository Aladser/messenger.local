<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контроллер поиска контактов */
class FindContactsController extends Controller
{
    public function actionIndex()
    {
        echo json_encode($this->model->getUsers($_POST['userphrase']));
    }
}
