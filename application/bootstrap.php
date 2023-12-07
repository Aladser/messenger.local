<?php

namespace Aladser;

use Aladser\Core\Config;
use Aladser\Core\Route;
use Aladser\Core\ScriptLinuxProcess;

require __DIR__.'/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

$os = explode(' ', php_uname())[0];
if ($os !== 'Windows') {
    $websocket = new ScriptLinuxProcess(
        Config::WEBSOCKET_PROCESSNAME,
        Config::getWebSocketProcessFile(),
        Config::getWebsocketProcessLogFile(),
        Config::getPidsListFile()
    );
    if (!$websocket->isActive()) {
        $websocket->run();
    }
}

Route::start();
