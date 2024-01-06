<?php

namespace App\Core;

use App\Models\ChatEntity;
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
    // чаты
    private ChatEntity $chats;

    public function __construct()
    {
        $this->connections = [];
        $this->connectionUsers = [];

        $this->chats = new ChatEntity();
        $this->messageEntity = new MessageEntity();
        $this->userEntity = new UserEntity();
    }

    /** Открыть соединение.
     * @param ConnectionInterface $conn соединение
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections[$conn->resourceId] = $conn;
        $message = json_encode(['onconnection' => $conn->resourceId]);
        $conn->send($message);
    }

    /** Закрыть соединение.
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
        $userChatMembersIdList = $this->chats->getUserPersonalChats($userId, true);
        $publicUsername = $this->userEntity->getPublicUsername($userId);
        $message = json_encode(['offconnection' => 1, 'user' => $publicUsername]);
        $this->sendMessage($userChatMembersIdList, $message);

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
            $userChatMembersIdList = $this->chats->getUserPersonalChats($userId, true);
            $message = json_encode($data);
            $this->sendMessage($userChatMembersIdList, $message);
            $from->send($message);

            echo "$data->author в сети\n";
        } elseif ($data->message) {
            // отправляется сообщение

            // id участников чата
            $participantsIds = $this->chats->getChatParticipantIds($data->chat);

            // формирование сообщения
            switch ($data->messageType) {
                case 'NEW':
                    $data->time = date('Y-m-d H:i:s');
                    $data->author_id = $this->userEntity->getIdByName($data->author);
                    $data->msg = $this->messageEntity->add($data);
                    $data->forward = 0;
                    unset($data->author_id);
                    break;
                case 'EDIT':
                    $data = $this->messageEntity->editMessage($data->message, $data->msgId);
                    break;
                case 'REMOVE':
                    $data = $this->messageEntity->removeMessage($data->msgId);
                    break;
                case 'FORWARD':
                    $data->time = date('Y-m-d H:i:s');
                    $data->author_id = $this->userEntity->getIdByName($data->author);
                    $data->message = $this->messageEntity->addForwarded($data);
                    unset($data->author_id);
            }

            // рассылка сообщения участникам чата
            $message = json_encode($data);
            echo "$message\n";
            $this->sendMessage($participantsIds, $message);
        }
    }

    /** Ошибка подключения.
     *
     * @param ConnectionInterface $conn соединение
     * @param \Exception          $e    ошибка
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "error: {$e->getMessage()}\n";
        $conn->close();
    }

    /** Отправить сообщение списку пользователей.
     *
     * @param array  $userChatMembersIdList список id получателей
     * @param string $message               сообщение
     */
    private function sendMessage(array $userChatMembersIdList, string $message): void
    {
        foreach ($userChatMembersIdList as $memberId) {
            if (array_key_exists($memberId, $this->connectionUsers)) {
                $connId = $this->connectionUsers[$memberId];
                $this->connections[$connId]->send($message);
            }
        }
    }
}
