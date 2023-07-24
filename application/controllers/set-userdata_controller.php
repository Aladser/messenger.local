<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class SetUserDataController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
