<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class CreateGroupController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
