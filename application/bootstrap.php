<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
date_default_timezone_set('Europe/Moscow');

// проверить существование папки data/temp.
$tempDir = __DIR__.'/data/temp';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777);
};

\core\Route::start();
?>
