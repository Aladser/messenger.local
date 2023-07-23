<?php

require __DIR__."/vendor/autoload.php";

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

// Объявляем сервер
$CONFIG = new core\ConfigClass();
$users = $CONFIG->getUsers();
$connections = $CONFIG->getConnections();
$messages = $CONFIG->getMessageDBTable();

$server = Ratchet\Server\IoServer::factory(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new core\Chat($connections, $messages, $users)
        )
    ),
    $CONFIG::CHAT_WS_PORT
);
$server->run();

// php chat-server.php
