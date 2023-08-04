<?php

namespace Aladser;

use Aladser\Core\Chat;
use Aladser\Core\ConfigClass;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

// чистка логов при запуске вебосокета
$logFilepath =  dirname(__DIR__, 1) . '/logs/websocket.log';
file_put_contents($logFilepath, '');

// Объявляем сервер
$CONFIG = new ConfigClass();
$users = $CONFIG->getUsers();
$connections = $CONFIG->getConnections();
$messages = $CONFIG->getMessageDBTable();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat($connections, $messages, $users)
        )
    ),
    $CONFIG::CHAT_WS_PORT
);
$server->run();