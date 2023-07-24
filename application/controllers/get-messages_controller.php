<?php

namespace Aladser\controllers;

use Aladser\core\Controller;

class GetMessagesController extends Controller
{
    public function action_index()
    {
        $this->model->run();
    }
}
