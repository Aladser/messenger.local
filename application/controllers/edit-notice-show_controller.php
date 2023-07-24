<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class EditNoticeShowController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
