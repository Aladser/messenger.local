<?php

namespace Aladser;

use Aladser\Core\ConfigClass;
use Aladser\Core\Route;

require __DIR__.'/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

$os = explode(' ', php_uname())[0];
if ($os !== 'Windows') {
    $websocket = new ScriptLinuxProcess(
        ConfigClass::WEBSOCKET_PROCESSNAME,
        ConfigClass::getWebSocketProcessFile(),
        ConfigClass::getWebsocketProcessLogFile(),
        ConfigClass::getPidsListFile()
    );
    if (!$websocket->isActive()) {
        $websocket->run();
    }
}

Route::start();
