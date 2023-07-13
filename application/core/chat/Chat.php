<?php
 
namespace core\chat;
use Ratchet\ConnectionInterface;
date_default_timezone_set('Europe/Moscow');

// Чат
class Chat implements \Ratchet\MessageComponentInterface {
    private $clients;           // хранение всех подключенных пользователей
    private $usersTable;        // таблица пользователей
    private $connectionsTable;  // таблица подключений
    private $messageTable;      // таблица сообщений
   
    public function __construct(\core\db\UsersDBTableModel $usersTable, \core\db\ConnectionsDBTableModel $connectionsTable, \core\db\MessageDBTableModel $messageTable) {
        $this->clients = new \SplObjectStorage;
        $this->usersTable = $usersTable;
        $this->connectionsTable = $connectionsTable;
        $this->messageTable = $messageTable;
        $this->connectionsTable->removeConnections(); // удаление старых соединений
    }

    /**
     * @param ConnectionInterface $conn соединение
     * открыть соединение
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // добавление клиента
        $message = json_encode([ 'onсonnection' => $conn->resourceId ]);
        foreach ($this->clients as $client) $client->send($message); // рассылка остальным клиентам
    }
    
    /**
     * @param ConnectionInterface $conn соединение
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
     * @param ConnectionInterface $from соединение
     * @param mixed $msg сообщение
     * получить соообщения от клиентов
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        // после соединения пользователь отправляет пакет messageOnconnection
        $data = json_decode($msg);
        if($data->messageOnconnection){
            $rslt = $this->connectionsTable->addConnection( ['author'=>$data->author, 'userId'=>$data->userId] ); // добавление соединения в БД
            $data->author = $rslt['publicUsername'] ? $rslt['publicUsername'] : ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo]; // имя пользователя или ошибка добавления
        }
        // сообщение пользователя
        else if($data->message){
            $data->time = date('Y-m-d H:i:s');  // '2023-07-11 12:00:00'
            $this->messageTable->addMessage($data); // добавление сообщения в БД
        }
        $msg = json_encode($data);
        echo "$msg\n";
        foreach ($this->clients as $client) $client->send($msg);
    }

    /**
     * @param ConnectionInterface $conn соединение
     * @param \Exception $e ошибка
     * ошибка подключения
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "error: {$e->getMessage()}\n";
        $conn->close();
    }
}