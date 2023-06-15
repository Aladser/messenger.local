<?php

namespace core;

class Route
{
	public static function start()
	{
		session_start();
		// конфиг
		$CONFIG = new ConfigClass();

		// контроллер и действие по умолчанию
		$routes = mb_substr($_SERVER['REDIRECT_URL'], 1);
		$controller_name = !empty($routes) ? $routes : 'Main';
        $action_name = 'index';
		
		// авторизация сохраняется в куки и сессии. Если авторизация есть, то переход messenger.local -> messenger.local/chats
		if( $controller_name === 'Main' && (isset($_SESSION['auth']) || isset($_COOKIE['auth'])) ){
			header('Location: /chats');
		}

		// добавляем префиксы
		$model_name = $controller_name.'_model';
		$controller_name = $controller_name.'_controller';
		$action_name = "action_$action_name";
		
		// подцепляем файл с классом модели (файла модели может и не быть)
		$model_file = strtolower($model_name).'.php';
		$model_path = "application/models/$model_file";
		if(file_exists($model_path))
		{
			include "application/models/$model_file";
		}

		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name).'.php';
		$controller_path = "application/controllers/$controller_file";
		if(file_exists($controller_path))
		{
			include "application/controllers/$controller_file";
		}
		else
		{
			Route::ErrorPage404();
		}

		//**** создаем модель, если существует
		if(file_exists($model_path)){
			$model_name = self::getMVCClassName($model_name, 'Model');
			$model = new $model_name($CONFIG);
		}

		//**** создаем контроллер
		$controller_name = self::getMVCClassName($controller_name, 'Controller');
		$controller = file_exists($model_path) ? new $controller_name($model) : new $controller_name();

		$action = $action_name;
		if(method_exists($controller, $action))
		{
			// вызываем действие контроллера
			$controller->$action($modelData);
		}
		else
		{
		    Route::ErrorPage404();
		}
	}
	
	public static function ErrorPage404()
	{
		$host = 'http://'.$_SERVER['HTTP_HOST'].'/';
		header('HTTP/1.1 404 Not Found');
		header("Status: 404 Not Found");
		header('Location:'.$host.'404');
		echo $host;
    }

	// сформировать имя класса из имени файла
	private static function getMVCClassName($name, $type){
		if($type === 'Model' || $type === 'Controller'){
			$name = explode('_', $name)[0]; // убирает _model или _controller
			$name = str_replace('-',' ',$name);
			$name = ucwords($name); //Преобразует в верхний регистр первый символ каждого слова в строке
			$name = str_replace(' ','',$name);
			$name = $name.$type;
			return $name;
		}
		else
			return null;
	}
}
?>