<?php
    // Подключаем файл зависимостей
    require "vendor/autoload.php";
    // Подключаем файл с классом Chat 
    require "Chat.php";
    
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
?>