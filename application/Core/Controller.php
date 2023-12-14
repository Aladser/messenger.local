<?php

namespace App\Core;

abstract class Controller
{
    public View $view;

    public function __construct()
    {
        $this->view = new View();
    }
}
