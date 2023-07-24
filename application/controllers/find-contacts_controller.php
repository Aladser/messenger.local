<?php

namespace Aladser\Controllers;

use Aladser\Core\Controller;

class FindContactsController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
