<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

/** контрллер проверки уникальности никнейма */
class IsUniqueNicknameController extends Controller
{
    public function actionIndex()
    {
        $this->model->run();
    }
}
