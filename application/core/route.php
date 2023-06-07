<?php
class Route
{
    
	public static function start()
	{
		// контроллер и действие по умолчанию
		$routes = mb_substr($_SERVER['REDIRECT_URL'], 1);
		$controller_name = !empty($routes) ? $routes : 'Main';
        $action_name = 'index';

		// добавляем префиксы
		$model_name = 'model_'.$controller_name;
		$controller_name = $controller_name.'_controller';
		$action_name = 'action_'.$action_name;
		// подцепляем файл с классом модели (файла модели может и не быть)
		$model_file = strtolower($model_name).'.php';
		$model_path = "application/models/".$model_file;
		if(file_exists($model_path))
		{
			include "application/models/".$model_file;
		}
		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name).'.php';
		$controller_path = "application/controllers/".$controller_file;
		if(file_exists($controller_path))
		{
			include "application/controllers/".$controller_file;
		}
		else
		{
			Route::ErrorPage404();
		}
		//**** создаем контроллер
		// формирование имени класса
		$controller_name = explode('_', $controller_name)[0];
		$controller_name = str_replace('-',' ',$controller_name);
		$controller_name = ucwords($controller_name);
		$controller_name = str_replace(' ','',$controller_name);
		$controller_name = $controller_name.'_Controller';

		$controller = new $controller_name;
		$action = $action_name;
		if(method_exists($controller, $action))
		{
			// вызываем действие контроллера
			$controller->$action();
		}
		else
		{
		    Route::ErrorPage404();
		}
	}
	
	static function ErrorPage404()
	{
                        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
                         header('HTTP/1.1 404 Not Found');
		header("Status: 404 Not Found");
		header('Location:'.$host.'404');
		echo $host;
    }

}
?>