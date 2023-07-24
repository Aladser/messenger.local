<?php

namespace Aladser;

use Aladser\core\Chat;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__."/vendor/autoload.php";

// Объявляем сервер
$CONFIG = new core\ConfigClass();
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

// php chat-server.php
