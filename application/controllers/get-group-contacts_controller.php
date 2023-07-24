<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class GetGroupContactsController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
