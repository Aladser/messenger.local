<?php

namespace Aladser;

use Aladser\Core\Route;
use Aladser\Core\ConfigClass;
use Aladser\Core\ScriptLinuxProcess;

require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

$websocket = new ScriptLinuxProcess(
    ConfigClass::WEBSOCKET_PROCESSNAME,
    ConfigClass::getWebSocketProcessFile(),
    ConfigClass::getWebsocketProcessLogFile(),
    ConfigClass::getPidsListFile()
);
if (!$websocket->isActive()) {
    $websocket->enable();
    // чистка логов веб-сервера
    file_put_contents(dirname(__DIR__, 1) . '/logs/access.log', '');
    file_put_contents(dirname(__DIR__, 1) . '/logs/error.log', '');
}

Route::start();