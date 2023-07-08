<?php
 
namespace core\chat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Чат
class Chat implements MessageComponentInterface {
    private $clients; // хранение всех подключенных пользователей
   
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->user = $user;
    }
   
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление нового пользователя
        echo "ON_CONNECTION {$conn->resourceId}\n";
        $message = json_encode([ 'onсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message); // рассылаем пользователям сообщение 
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn); // Отключаем клиента
        echo "OFF_CONNECTION {$conn->resourceId}\n";
        $message = json_encode([ 'offсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // после соединения пользователь отправляет пакет с id подключения и именем. Данные записываются в БД
        $data = json_decode($msg);
        if($data->messageOnconnection){
            var_dump($data);
        }
        else{
            echo "$msg\r\n";
        }
        foreach ($this->clients as $client) $client->send($msg);
    }

    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "ОШИБКА: {$e->getMessage()}\r\n";
        $conn->close();
    }
}