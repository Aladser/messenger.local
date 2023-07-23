<?php

namespace core;

class Controller
{
    public $view;
    public $model;
    
    public function __construct($modelName=null)
    {
        $this->view = new View();
        $this->model = is_null($modelName) ? null : $modelName;
    }
}
