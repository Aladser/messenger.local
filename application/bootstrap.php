<?php

namespace Aladser;

use Aladser\Core\Route;
use Aladser\Core\ScriptLinuxProcess;

require_once __DIR__.'/vendor/autoload.php';
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
        // чистка логов
        $rootDir = dirname('.');
        exec("echo > $rootDir/logs/access.log");
        exec("echo > $rootDir/logs/error.log");
        exec("echo > $rootDir/logs/pids.log");
        exec("echo > $rootDir/logs/websocket.log");
        // запуск вебсокета
        $websocket->run();
    }
}

Route::start();
