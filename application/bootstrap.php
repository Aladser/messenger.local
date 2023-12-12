<?php

namespace Aladser;

use Aladser\Core\Route;
use Aladser\Core\ScriptLinuxProcess;

require_once __DIR__.'/vendor/autoload.php';
date_default_timezone_set('Europe/Moscow');

// php /var/www/messenger.local/application/chat-server.php > /var/www/messenger.local/logs/websocket.log
$os = explode(' ', php_uname())[0];
if ($os !== 'Windows') {
    $websocket = new ScriptLinuxProcess(
        config('WEBSOCKET_PROCESS_NAME'),
        config('WEBSOCKET_PROCESS_FILE'),
        config('WEBSOCKET_PROCESS_LOGFILE'),
        config('PIDLIST_FILE')
    );
    if (!$websocket->isActive()) {
        $rootDir = dirname(__DIR__, 1);
        // чистка логов
        exec("echo > $rootDir/logs/access.log");
        exec("echo > $rootDir/logs/error.log");
        exec("echo > $rootDir/logs/pids.log");
        exec("echo > $rootDir/logs/websocket.log");

        // запуск вебсокета
        $websocket->run();
    }
}

Route::start();
