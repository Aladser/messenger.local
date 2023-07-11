<?php
 
namespace core\chat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Чат
class Chat implements MessageComponentInterface {
    private $clients; // хранение всех подключенных пользователей
    private $usersTable; // таблица пользователей
    private $connectionsTable; // таблица подключений
   
    public function __construct($usersTable, $connectionsTable) {
        $this->clients = new \SplObjectStorage;
        $this->usersTable = $usersTable;
        $this->connectionsTable = $connectionsTable;
        $this->connectionsTable->removeConnections(); // удаление старых соединений
    }

    /**
     * Открыть соединение
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление клиента
        $message = json_encode([ 'onсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message); // рассылка остальным клиентам
    }
    
    /**
     * Закрыть соединение
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $publicUsername = $this->connectionsTable->getConnectionPublicUsername( $conn->resourceId ); // публичное имя клиента
        $this->connectionsTable->removeConnection( $conn->resourceId ); // удаление соединения из БД
        echo "connection $publicUsername completed\n";
        $message = json_encode([ 'offсonnection' => 1, 'user' => $publicUsername]);
        foreach ($this->clients as $client) $client->send($message); 
    }

    /**
     * получение соообщений от клиентов
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // после соединения пользователь отправляет пакет messageOnconnection
        $data = json_decode($msg);
        if($data->messageOnconnection){
            $rslt = $this->connectionsTable->addConnection( ['author'=>$data->author, 'userId'=>$data->userId] ); // добавление соединения в БД
            $data->author = $rslt['publicUsername'] ? $rslt['publicUsername'] : ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo]; // имя пользователя или ошибка добавления
            $msg = json_encode($data);
        }
        echo "$msg\n";
        foreach ($this->clients as $client) $client->send($msg);
    }

    /**
     * ошибка подключения
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "error: {$e->getMessage()}\n";
        $conn->close();
    }
}