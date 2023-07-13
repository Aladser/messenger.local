<?php

spl_autoload_register(function ($class_name) {include $class_name . '.php';});

// проверить существование папки data/temp. Иногда не клонируется из репозитория
$tempDir = __DIR__.'/data/temp';
if(!is_dir($tempDir)){mkdir($tempDir, 0777);};

\core\Route::start();