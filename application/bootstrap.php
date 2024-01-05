<?php

namespace App;

use App\Controllers\MainController;
use App\Core\Route;
use App\Core\ScriptLinuxProcess;

require_once __DIR__.'/vendor/autoload.php';

// проверка CSRF
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['CSRF'])) {
        if ($_POST['CSRF'] !== $_SESSION['CSRF']) {
            http_response_code(419);
            $controller = new MainController();
            $controller->error('Access is denied');

            return;
        }
    } else {
        $controller = new MainController();
        $controller->error('No csrf');

        return;
    }
}

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
        // чистка логов
        $rootDir = dirname(__DIR__, 1);
        exec("echo > $rootDir/logs/access.log");
        exec("echo > $rootDir/logs/error.log");
        exec("echo > $rootDir/logs/pids.log");
        exec("echo > $rootDir/logs/websocket.log");

        // запуск вебсокета
        $websocket->run();
    }
}

Route::start();
