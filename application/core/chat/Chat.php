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
        $this->user = $user;
        $this->usersTable = $usersTable;
        $this->connectionsTable = $connectionsTable;
        $this->connectionsTable->clearConnections();
    }
   
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление нового пользователя
        $message = json_encode([ 'onсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message); // рассылаем пользователям сообщение 
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn); // Отключаем клиента
        $userEmail = $this->connectionsTable->getConnectionUserEmail( $conn->resourceId );
        $this->connectionsTable->removeConnection( $conn->resourceId );

        echo "CONNECTION $userEmail COMPLETED\n";
        $message = json_encode([ 'offсonnection' => $userEmail ]);
        foreach ($this->clients as $client) $client->send($message);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // пакет о подключении. после соединения пользователь отправляет пакет с id подключения и именем. Данные записываются в БД
        $data = json_decode($msg);
        if($data->messageOnconnection){
            $rslt = $this->connectionsTable->addConnection( ['author'=>$data->author, 'userId'=>$data->userId] );
            if($rslt['publicUsername']){
                $data->author = $rslt['publicUsername'];
            }
            else{
                $data = ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo];
            }
            $msg = json_encode($data);
        }

        echo "$msg\n";
        foreach ($this->clients as $client) $client->send($msg);
    }

    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "ERROR: {$e->getMessage()}\n";
        $conn->close();
    }
}