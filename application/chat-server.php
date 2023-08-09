<?php

namespace Aladser;

use Aladser\Core\Chat;
use Aladser\Core\ConfigClass;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

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