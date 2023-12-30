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
    // массив подключенных пользователей
    private array $connections;
    // подключения
    private ConnectionEntity $connectionEntity;
    // сообщения
    private MessageEntity $messageEntity;
    // пользователи
    private UserEntity $userEntity;

    public function __construct()
    {
        $this->connections = [];
        $this->messageEntity = new MessageEntity();
        $this->userEntity = new UserEntity();
        $this->connectionEntity = new ConnectionEntity();
        $this->connectionEntity->removeConnections(); // удаление старых соединений
    }

    /** открыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo json_encode($conn)."\n";
        // добавление клиента
        $this->connections[$conn->resourceId] = $conn;

        $message = json_encode(['onconnection' => $conn->resourceId]);

        foreach ($this->connections as $client) {
            $client->send($message); // рассылка остальным клиентам
        }
    }

    /** закрыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onClose(ConnectionInterface $conn)
    {
        echo json_encode($conn)."\n";
        // удаление соединения
        unset($this->connections[$conn->resourceId]);

        // публичное имя клиента
        $publicUsername = $this->connectionEntity->getConnectionPublicUsername($conn->resourceId);
        // удаление соединения из БД
        $this->connectionEntity->removeConnection($conn->resourceId);
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);

        foreach ($this->connections as $client) {
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
            if (!$this->connectionEntity->exists($data->wsId)) {
                $connection = $this->connectionEntity->add(['author' => $data->author, 'wsId' => $data->wsId]);
            }
            // имя пользователя или ошибка добавления
            if ($connection['publicUsername']) {
                $data->author = $connection['publicUsername'];
            } else {
                $data->author = ['messageOnconnection' => 1, 'systeminfo' => $data->systeminfo];
            }
            // рассылка сообщения всем
            $message = json_encode($data);
            foreach ($this->connections as $client) {
                $client->send($message);
            }
        } elseif ($data->message) {
            // отправляется сообщение

            // id участников чата
            $participantsIds = $this->messageEntity->getChatParticipantIds($data->chat);
            // формирование данных сообщения
            switch ($data->messageType) {
                case 'NEW':
                    // формирование сообщения
                    $data->time = date('Y-m-d H:i:s');
                    $data->msg = $this->messageEntity->add($data);
                    $data->forward = 0;
                    break;
                case 'EDIT':
                    $data = $this->messageEntity->editMessage($data->message, $data->msgId);
                    break;
                case 'REMOVE':
                    $data = $this->messageEntity->removeMessage($data->msgId);
                    break;
                case 'FORWARD':
                    $data->time = date('Y-m-d H:i:s');
                    $data->msgId = intval($data->msgId);
                    $data->authorId = intval($this->userEntity->getIdByName($data->author));
                    $data->message = $this->messageEntity->addForwardedMessage($data);
            }
            // рассылка сообщения участникам чата
            $message = json_encode($data);
            foreach ($participantsIds as $participantId) {
                $connId = $this->connectionEntity->getUserConnId($participantId);
                if ($connId) {
                    $this->connections[$connId]->send($message);
                }
            }
        }
    }

    /** ошибка подключения.
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
