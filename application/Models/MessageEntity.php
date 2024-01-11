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
            select messages.id as msg, 
            chat_id as chat, 
            getPublicUserName(email, nickname, hide_email) as author, 
            content as message, 
            time, forward
            from messages join users on users.id = creator_user_id
            where chat_id = :chatId
            order by time
        ';

        return $this->dbQuery->queryPrepared($sql, ['chatId' => $chatId], false);
    }

    // Добавить сообщение
    public function add($message)
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
        // добавить копию сообщения в указанный чат
        $messageId = $this->add($message);
        // установить флаг "пересылка сообщения"
        $fieldArray = ['forward' => 1];
        $condition = [
            'condition_field_name' => 'id',
            'condition_sign' => '=',
            'condition_field_value' => $messageId,
        ];
        $isUpdated = $this->dbQuery->update('messages', $fieldArray, $condition);

        return $isUpdated;
    }

    // изменить сообщение
    public function editMessage(string $msg, int $msgId)
    {
        // изменяем строку
        $this->dbQuery->exec("update messages set content = '$msg' where id = $msgId");
        // возвращаем строку
        $sql = 'select id as msg, chat_id as chat, content as message, time 
        from messages where id = :msgId';
        $args = ['msgId' => $msgId];
        $rslt = $this->dbQuery->queryPrepared($sql, $args);
        $rslt['messageType'] = 'EDIT';

        return $rslt;
    }

    // удалить сообщение
    public function removeMessage(int $msgId)
    {
        // получим удаляемую строку
        $sql = 'select id as msg, chat_id as chat 
        from messages where id = :msgId';
        $args = ['msgId' => $msgId];
        $rowDeleted = $this->dbQuery->queryPrepared($sql, $args);
        $rowDeleted['messageType'] = 'REMOVE';
        // удаляем
        $this->dbQuery->exec("delete from messages where id = $msgId");

        return $rowDeleted;
    }
}
