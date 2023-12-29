<?php

namespace App\Core;

use App\Models\ConnectionEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/** Чат - серверная часть */
class ChatWebsocketServer implements MessageComponentInterface
{
    private \SplObjectStorage $clients;           // хранение всех подключенных пользователей
    private ConnectionEntity $connections;  // таблица подключений
    private MessageEntity $messages;      // таблица сообщений
    private UserEntity $users;        // таблица пользователей

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->connections = new ConnectionEntity();
        $this->messages = new MessageEntity();
        $this->users = new UserEntity();
        $this->connections->removeConnections(); // удаление старых соединений
    }

    /** открыть соединение.
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

    /** закрыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        // публичное имя клиента
        $publicUsername = $this->connections->getConnectionPublicUsername($conn->resourceId);
        // удаление соединения из БД
        $this->connections->removeConnection($conn->resourceId);
        echo "Connection $publicUsername completed\n";
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /** получить/отправить соообщения.
     * @param ConnectionInterface $from    соединение
     * @param mixed               $message сообщение
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        echo "$message\n";
        $data = json_decode($message);
        if (property_exists($data, 'messageOnconnection')) {
            // после соединения пользователь отправляет пакет messageOnconnection.

            // добавление соединения в БД
            $connection = $this->connections->addConnection(['author' => $data->author, 'wsId' => $data->wsId]);
            // имя пользователя или ошибка добавления
            if ($connection['publicUsername']) {
                $data->author = $connection['publicUsername'];
            } else {
                $data->author = ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo];
            }
        } elseif ($data->message) {
            // отправляется сообщение

            $data->message = htmlspecialchars($data->message); // экранирование символов
            switch ($data->messageType) {
                case 'NEW':
                    $data->time = date('Y-m-d H:i:s');
                    $data->msg = $this->messages->add($data);
                    $data->forward = 0;
                    break;
                case 'EDIT':
                    $data = $this->messages->editMessage($data->message, $data->msgId);
                    break;
                case 'REMOVE':
                    $data = $this->messages->removeMessage($data->msgId);
                    break;
                case 'FORWARD':
                    $data->time = date('Y-m-d H:i:s');
                    $data->msgId = intval($data->msgId);
                    $data->authorId = intval($this->users->getUserIdByEmail($data->author));
                    $data->message = $this->messages->addForwardedMessage($data);
            }
        }

        $message = json_encode($data);
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /**
     * ошибка подключения.
     *
     * @param ConnectionInterface $conn соединение
     * @param \Exception          $e    ошибка
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "error: {$e->getMessage()}\n";
        $conn->close();
    }
}
