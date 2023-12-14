<?php

namespace App;

use App\Core\ChatWebsocketServer;
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
    config('WEBSOCKET_PORT')
);
$server->run();
