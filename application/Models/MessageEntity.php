<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы сообщений чатов */
class MessageEntity extends Model
{
    // возвращает сообщения диалога
    public function getMessages(int $chatId)
    {
        $sql = '
            select messages.id as messages_id, 
            getPublicUserName(email, nickname, hide_email) as author_name, 
            content as message_text,
            chats.type as chat_type, 
            time, forward
            from messages join users on users.id = creator_user_id
            join chats on chats.id = messages.chat_id
            where chat_id = :chatId
            order by time
        ';

        $rows = $this->dbQuery->queryPrepared($sql, ['chatId' => $chatId], false);
        $messageArr = [];
        // фильтрация данных
        foreach ($rows as $row) {
            $messageArr[] = [
                'author_name' => $row['author_name'],
                'chat_type' => $row['chat_type'],
                'forward' => $row['forward'],
                'message_text' => $row['message_text'],
                'message_id' => $row['messages_id'],
                'time' => $row['time'],
            ];
        }

        return $messageArr;
    }

    public function getContent($id)
    {
        $sql = 'select content from messages where id = :id';
        $args = ['id' => $id];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);
        $content = $queryResult ? $queryResult['content'] : false;

        return $content;
    }

    // Добавить сообщение
    public function add($message, $isForwarded = false)
    {
        $userData = [
            'chat_id' => $message->chat_id,
            'content' => $message->message_text,
            'creator_user_id' => $message->author_id,
        ];
        $messageId = $this->dbQuery->insert('messages', $userData);

        return $messageId;
    }

    // Добавить пересылаемое сообщение
    public function addForwarded($message)
    {
        $userData = [
            'chat_id' => $message->chat_id,
            'content' => $message->message_text,
            'creator_user_id' => $message->author_id,
            'forward' => 1,
        ];
        $messageId = $this->dbQuery->insert('messages', $userData);

        return $messageId;
    }

    // изменить сообщение
    public function editMessage(string $msg, int $msgId)
    {
        // изменяем строку
        $this->dbQuery->exec("update messages set content = '$msg' where id = $msgId");
        // возвращаем строку
        $sql = 'select messages.id as message_id, 
            content as message_text, time 
            from messages
            where messages.id = :msgId
        ';
        $args = ['msgId' => $msgId];
        $rslt = $this->dbQuery->queryPrepared($sql, $args);

        return $rslt;
    }

    // удалить сообщение
    public function removeMessage(int $msgId): bool
    {
        $rowsDeleted = $this->dbQuery->exec("delete from messages where id = $msgId");

        return $rowsDeleted > 0;
    }
}
