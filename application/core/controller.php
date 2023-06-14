<?php
class Controller {
	public $view;
	public $model;
	
	function __construct($model_name=null)
	{
		$this->view = new View();
		$this->model = is_null($model_name) ? null : $model_name;
	}
}