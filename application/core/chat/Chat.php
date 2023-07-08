<?php
 
 namespace core\chat;
// Подключаем нужные компоненты
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Создаём новый класс для обработки чата
class Chat implements MessageComponentInterface {
    private $clients; // Свойство для хранения всех подключенных пользователей
    private $connectionsFile = __DIR__.'/connections.data'; // подключения пользователей
   
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

        echo "$msg\r\n";
        foreach ($this->clients as $client) $client->send($msg);
    }

    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "ОШИБКА: {$e->getMessage()}\r\n";
        $conn->close();
    }
}