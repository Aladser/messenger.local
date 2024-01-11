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

            $senderId = $this->userEntity->getIdByName($data->author);
            $data->author = $this->userEntity->getPublicUsername($senderId);
            // добавление подключения пользователя в массив подключений
            if (!array_key_exists($senderId, $this->connectionUsers)) {
                $this->connectionUsers[$senderId] = $data->wsId;
            }
            echo "$data->author в сети\n";
        } elseif ($data->message_type) {
            // отправляется сообщение
            var_dump($data);

            $senderId = array_search($from->resourceId, $this->connectionUsers);
            $senderPublicName = false;
            if (!$senderId) {
                echo "Подключение $from->resourceId не найдено";

                return;
            } else {
                $senderPublicName = $this->userEntity->getPublicUsername($senderId);
            }

            switch ($data->message_type) {
                case 'NEW':
                    $data->time = date('Y-m-d H:i:s');
                    $data->author_id = $senderId;

                    switch ($data->chat_type) {
                        case 'personal':
                            $contactId = $this->userEntity->getIdByName($data->chat_name);
                            $chatId = $this->chats->getPersonalChatId($senderId, $contactId);
                            break;
                        case 'group':
                            $chatId = $this->chats->getGroupChatId($data->chat_name);
                            break;
                        default:
                            throw "Неверный chat_type = $chat_type";
                    }

                    $data->chat_id = $chatId;
                    $data->message_id = $this->messageEntity->add($data);
                    $data->author_name = $senderPublicName;
                    $data->forward = 0;
                    unset($data->author_id);
                    unset($data->chat_id);
                    break;
                case 'EDIT':
                    $data = $this->messageEntity->editMessage($data->message, $data->msgId);
                    break;
                case 'REMOVE':
                    $isDeleted = $this->messageEntity->removeMessage($data->message_id);
                    if ($isDeleted) {
                        unset($data->message_text);
                    } else {
                        $data = ['error' => 'Ошибка удаления сообщения'];
                    }
                    break;
                case 'FORWARD':
                    $data->time = date('Y-m-d H:i:s');
                    $data->author_id = $senderId;
                    $data->message = $this->messageEntity->addForwarded($data);
                    unset($data->author_id);
            }
            echo json_encode($data)."\n";
        }

        $userChatMembersIdList = $this->chats->getUserPersonalChats($senderId, true);
        $message = json_encode($data);
        $from->send($message);
        $this->sendMessage($userChatMembersIdList, $message);
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
