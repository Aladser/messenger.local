<?php

namespace Aladser;

use Aladser\Core\ChatWebsocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__.'/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatWebsocketServer()
        )
    ),
    Config::CHAT_WS_PORT
);
$server->run();
