<?php
    namespace core\chat;
    // Подключаем файл зависимостей
    require dirname(__DIR__, 2)."/vendor/autoload.php";
    require 'Chat.php';

    // Подключаем все зависимости
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    
    // Объявляем сервер
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8888
    );

    // Запускаем сервер
    $server->run();


