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
if (!$websocket->isActive()){
    $websocket->enable();
}

Route::start();