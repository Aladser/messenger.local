<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class LoginUserController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
