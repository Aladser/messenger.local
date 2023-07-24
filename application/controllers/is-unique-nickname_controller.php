<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class IsUniqueNicknameController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
