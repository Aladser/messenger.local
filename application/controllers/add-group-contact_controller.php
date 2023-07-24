<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class AddGroupContactController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
