<?php

namespace Aladser\Core;

use Aladser\Models\ConnectionsDBTableModel;
use Aladser\Models\MessageDBTableModel;
use Aladser\Models\UsersDBTableModel;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/** Чат - серверная часть */
class Chat implements MessageComponentInterface
{
    private \SplObjectStorage $clients;           // хранение всех подключенных пользователей
    private ConnectionsDBTableModel $connectionsTable;  // таблица подключений
    private MessageDBTableModel $messageTable;      // таблица сообщений
    private UsersDBTableModel $usersTable;        // таблица пользователей

    public function __construct(
        ConnectionsDBTableModel $connectionsTable,
        MessageDBTableModel     $messageTable,
        UsersDBTableModel       $usersTable
    ) {
        $this->clients = new \SplObjectStorage;
        $this->connectionsTable = $connectionsTable;
        $this->messageTable = $messageTable;
        $this->usersTable = $usersTable;
        $this->connectionsTable->removeConnections(); // удаление старых соединений
    }

    /** открыть соединение
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn); // добавление клиента
        $message = json_encode(['onconnection' => $conn->resourceId]);
        echo "$message\n";
        foreach ($this->clients as $client) {
            $client->send($message); // рассылка остальным клиентам
        }
    }

    /** закрыть соединение
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
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /** получить/отправить соообщения
     * @param ConnectionInterface $from соединение
     * @param mixed $msg сообщение
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        if (property_exists($data, 'messageOnconnection')) {
            // после соединения пользователь отправляет пакет messageOnconnection.

            // добавление соединения в БД
            $connection = $this->connectionsTable->addConnection(['author' => $data->author, 'wsId' => $data->wsId]);
            // имя пользователя или ошибка добавления
            if ($connection['publicUsername']) {
                $data->author = $connection['publicUsername'];
            } else {
                $data->author = ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo];
            }
        } elseif ($data->message) {
            // отправляется сообщение

            $data->message = htmlspecialchars($data->message); // экранирование символов
            if ($data->messageType == 'NEW') {
                $data->time = date('Y-m-d H:i:s');
                $data->msg = $this->messageTable->addMessage($data);
            } elseif ($data->messageType == 'EDIT') {
                $data = $this->messageTable->editMessage($data->message, $data->msgId);
            } elseif ($data->messageType == 'REMOVE') {
                $data = $this->messageTable->removeMessage($data->msgId);
            } elseif ($data->messageType == 'FORWARD') {
                $data->time = date('Y-m-d H:i:s');
                $data->msgId = intval($data->msgId);
                $data->authorId = intval($this->usersTable->getUserId($data->author));
                $data->msg = $this->messageTable->addForwardedMessage($data);
            }
        }

        $msg = json_encode($data);
        echo "$msg\n";
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    /**
     * ошибка подключения
     * @param ConnectionInterface $conn соединение
     * @param Exception $e ошибка
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "error: {$e->getMessage()}\n";
        $conn->close();
    }
}
