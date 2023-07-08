<?php
 
 namespace core\chat;
// Подключаем нужные компоненты
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Создаём новый класс для обработки чата
class Chat implements MessageComponentInterface {
    private $clients; // Свойство для хранения всех подключенных пользователей
    private $user; // пользователь-хост
   
    public function __construct($user='user') {
        $this->clients = new \SplObjectStorage;
        $this->user = $user;
    }
   
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление нового пользователя
        $message = "ON_CONNECTION";
        echo "$message\n";
        $message = json_encode([ 'system' => $message ]);
        foreach ($this->clients as $client) $client->send($message); // рассылаем пользователям сообщение 
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn); // Отключаем клиента
        $message = "OFF_CONNECTION";
        echo "$message\n";
        $message = json_encode([ 'system' => $message ]);
        foreach ($this->clients as $client) $client->send($message);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "СООБЩЕНИЕ: $msg\r\n";
        foreach ($this->clients as $client) $client->send($msg);
    }

    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "ОШИБКА: {$e->getMessage()}\r\n";
        $conn->close();
    }
}