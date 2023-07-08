<?php
    require __DIR__."/vendor/autoload.php";
    use core\chat\Chat;
    use core\ConfigClass;
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;

    spl_autoload_register(function ($class_name) {include $class_name . '.php';});
    
    // Объявляем сервер
    $CONFIG = new ConfigClass();
    $users = $CONFIG->getUsers();
    $connections = $CONFIG->getConnections();
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat($users, $connections)
            )
        ),
        $CONFIG::CHAT_WS_PORT
    );

    $server->run();

//cd C:\domains\messenger.local\application
// php chat-server.php
// php C:\domains\messenger.local\application\chat-server.php

