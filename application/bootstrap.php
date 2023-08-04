<?php

namespace Aladser;

use Aladser\Core\Route;

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

//**** поиск запущенного демона вебсокета ****
$pidFile = dirname(__DIR__, 1).'/logs/pids.log';
$fd = fopen($pidFile, 'w');
fwrite($fd, ''); // чистка файла от старых данных
exec("ps aux | grep chat-server > $pidFile"); // новая таблица pidов
$isWebSocket = count(file($pidFile)) > 2; // чтение новой таблицы pidов, поиск запущенного демона
fclose($fd);

$filepath = __DIR__ . '/chat-server.php';
$logFilepath =  dirname(__DIR__, 1) . '/logs/websocket.log';
// запуск вебосокета, если не запущен
if (!$isWebSocket) {
    exec("php $filepath > $logFilepath &");
}

Route::start();
