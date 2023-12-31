<?php

namespace App\Core;

use App\Models\ContactEntity;
use App\Models\MessageEntity;
use App\Models\UserEntity;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/** Чат - серверная часть */
class ChatWebsocketServer implements MessageComponentInterface
{
    // массив подключений
    private array $connections;
    // [id пользователя => websocketId]
    private array $connectionUsers;
    // сообщения
    private MessageEntity $messageEntity;
    // пользователи
    private UserEntity $userEntity;
    // контакты
    private ContactEntity $сontactEntity;

    public function __construct()
    {
        $this->connections = [];
        $this->connectionUsers = [];

        $this->messageEntity = new MessageEntity();
        $this->userEntity = new UserEntity();
        $this->contactEntity = new ContactEntity();
    }

    /** открыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections[$conn->resourceId] = $conn;
        $message = json_encode(['onconnection' => $conn->resourceId]);
        $conn->send($message);
    }

    /** закрыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onClose(ConnectionInterface $conn)
    {
        // удаление соединения
        unset($this->connections[$conn->resourceId]);

        // удаление записи из массива [Пользователь => WebsocketId]
        $userId = array_search($conn->resourceId, $this->connectionUsers);
        unset($this->connectionUsers[$userId]);

        // рассылка контактам пользователя и себе о подключении
        $contactIdList = $this->contactEntity->getUserContacts($userId, true);
        $publicUsername = $this->userEntity->getPublicUsername($userId);
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);
        $this->sendMessage($contactIdList, $message);

        echo "$publicUsername не в сети\n";
    }

    /** получить/отправить соообщения.
     * @param ConnectionInterface $from    соединение
     * @param mixed               $message сообщение
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $data = json_decode($message);

        if (property_exists($data, 'messageOnconnection')) {
            // после соединения пользователь отправляет пакет messageOnconnection.
            $userId = $this->userEntity->getIdByName($data->author);
            $data->author = $this->userEntity->getPublicUsername($userId);

            // добавление подключения пользователя в массив подключений
            if (!array_key_exists($userId, $this->connectionUsers)) {
                $this->connectionUsers[$userId] = $data->wsId;
            }

            // рассылка контактам пользователя и себе о подключении
            $contactIdList = $this->contactEntity->getUserContacts($userId, true);
            $message = json_encode($data);
            $this->sendMessage($contactIdList, $message);
            $from->send($message);

            echo "$data->author в сети\n";
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
                if (array_key_exists($participantId, $this->connectionUsers)) {
                    $connId = $this->connectionUsers[$participantId];
                    $this->connections[$connId]->send($message);
                }
            }

            echo "$message\n";
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

    private function sendMessage(array $contactIdList, string $message): void
    {
        foreach ($contactIdList as $contactId) {
            if (array_key_exists($contactId, $this->connectionUsers)) {
                $connId = $this->connectionUsers[$contactId];
                $this->connections[$connId]->send($message);
            }
        }
    }
}
