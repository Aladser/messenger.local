<?php
class Controller {
	public $view;
	public $model;
	
	function __construct($modelName=null)
	{
		$this->view = new View();
		$this->model = is_null($modelName) ? null : $modelName;
	}
}