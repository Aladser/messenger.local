<?php

namespace Aladser\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Aladser\Core\DB\ConnectionsDBTableModel;
use Aladser\Core\DB\MessageDBTableModel;
use Aladser\Core\DB\UsersDBTableModel;

/** Чат - серверная часть */
class Chat implements MessageComponentInterface
{
    private $clients;           // хранение всех подключенных пользователей
    private $connectionsTable;  // таблица подключений
    private $messageTable;      // таблица сообщений
    private $usersTable;      // таблица сообщений
    private $logFile;
    private $logfileContent;
   
    public function __construct(
        ConnectionsDBTableModel $connectionsTable,
        MessageDBTableModel $messageTable,
        UsersDBTableModel $usersTable
    ) {
        $this->clients = new \SplObjectStorage;
        $this->connectionsTable = $connectionsTable;
        $this->messageTable = $messageTable;
        $this->usersTable = $usersTable;
        $this->connectionsTable->removeConnections(); // удаление старых соединений
        // обнуления содержания файла логов
        $this->logFile = dirname(__DIR__, 1).'/logs.txt';
        file_put_contents($this->logFile, "");
    }

    /**
     * открыть соединение
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn); // добавление клиента
        $message = json_encode(['onconnection' => $conn->resourceId]);
        foreach ($this->clients as $client) {
            $client->send($message); // рассылка остальным клиентам
        }
    }
    
    /**
     * закрыть соединение
     * @param ConnectionInterface $conn соединение
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        // публичное имя клиента
        $publicUsername = $this->connectionsTable->getConnectionPublicUsername($conn->resourceId);
        // удаление соединения из БД
        $this->connectionsTable->removeConnection($conn->resourceId);
        echo "Connection $publicUsername completed\n";
        $this->writeLog("Connection $publicUsername completed");
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /**
     * получить соообщения от клиентов
     * @param ConnectionInterface $from соединение
     * @param mixed $msg сообщение
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        // после соединения пользователь отправляет пакет messageOnconnection. Или отправляется сообщение
        if (property_exists($data, 'messageOnconnection')) {
            // добавление соединения в БД
            $connection = $this->connectionsTable->addConnection(['author'=>$data->author, 'wsId'=>$data->wsId]);
            // имя пользователя или ошибка добавления
            if ($connection['publicUsername']) {
                $data->author = $connection['publicUsername'];
            } else {
                $data->author = ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo];
            }
        } elseif ($data->message) {
            if ($data->messageType == 'NEW') {
                $data->time = date('Y-m-d H:i:s');
                $data->chat_message_id = $this->messageTable->addMessage($data);
            } elseif ($data->messageType == 'EDIT') {
                $data = $this->messageTable->editMessage($data->message, $data->msgId);
            } elseif ($data->messageType == 'REMOVE') {
                $data = $this->messageTable->removeMessage($data->msgId);
            } elseif ($data->messageType == 'FORWARD') {
                $data->time = date('Y-m-d H:i:s');
                $data->msgId = intval($data->msgId);
                $data->fromuserId = intval($this->usersTable->getUserId($data->fromuser));
                $data->chat_message_id = $this->messageTable->addForwardedMessage($data);
            }
        }
        $msg = json_encode($data);
        echo $msg."\n";
        $this->writeLog($msg);
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    /**
     * ошибка подключения
     * @param ConnectionInterface $conn соединение
     * @param \Exception $e ошибка
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->writeLog("error: {$e->getMessage()}");
        $conn->close();
    }

    /**
     * Запись логов
     * @param mixed $message лог
     */
    public function writeLog($message)
    {
        file_put_contents($this->logFile, $message."\n", FILE_APPEND | LOCK_EX);
    }
}
