<?php

namespace Aladser;

use Aladser\Core\Route;

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

//**** поиск запущенного демона вебсокета ****
$pidFile = __DIR__.'/pids.txt';
$fd = fopen($pidFile, 'w');
fwrite($fd, ''); // чистка файла от старых данных
exec("ps aux | grep chat-server > $pidFile"); // новая таблица pidов
$isWebSocket = count(file($pidFile)) > 2; // чтение новой таблицы pidов, поиск запущенного демона
fclose($fd);

Route::start();
