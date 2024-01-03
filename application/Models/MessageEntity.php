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

    // получить ID диалога
    public function getDialogId($user1Id, $user2Id)
    {
        $sql = "
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user1Id and type='dialog'
            intersect
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user2Id and type='dialog'
        ";
        $args = ['user1Id' => $user1Id, 'user2Id' => $user2Id];
        $chatId = $this->dbQuery->queryPrepared($sql, $args)['chat_id'];

        return $chatId;
    }

    /** получить ID группового чата*/
    public function getDiscussionId(string $groupName)
    {
        return $this->dbQuery->queryPrepared(
            'select id from chats where name = :groupName',
            ['groupName' => $groupName],
            false
        )[0]['chat_id'];
    }

    // получить id получателей сообщения
    public function getChatParticipantIds($chatId)
    {
        $sql = 'select user_id as recipient from chat_participants 
            where chat_id = :chatId';
        $args = ['chatId' => $chatId];
        $queryResultData = $this->dbQuery->queryPrepared($sql, $args, false);
        $recipientIdArray = [];
        foreach ($queryResultData as $element) {
            array_push($recipientIdArray, $element['recipient']);
        }

        return $recipientIdArray;
    }

    /** удалить чат */
    public function removeChat($dialogId)
    {
        $result = $this->dbQuery->exec("delete from chat_participants where chat_id  = $dialogId");
        $result += $this->dbQuery->exec("delete from messages where chat_id  = $dialogId");
        $result += $this->dbQuery->exec("delete from chats where id = $dialogId");

        return $result;
    }

    // создать групповой чат
    public function createDiscussion(int $userHostId)
    {
        $groupId = $this->dbQuery->executeProcedure("create_discussion($userHostId, @info)", '@info');
        $sql = 'select id as chat, name from chats where id = :groupId';

        return $this->dbQuery->queryPrepared($sql, ['groupId' => $groupId]);
    }

    // возвращает групповые чаты пользователя
    public function getDiscussions(int $userHostId)
    {
        $sql = "
        select chats.id as chat, name, notice       
        from chat_participants
        join chats on chat_participants.chat_id = chats.id
        where type = 'discussion' and user_id = :userHostId
        ";

        return $this->dbQuery->queryPrepared($sql, ['userHostId' => $userHostId], false);
    }

    // возвращает создателя группового чата
    public function getDiscussionCreatorId($chatId)
    {
        $sql = 'select creator_id from chats where id = :chatId';
        $args = ['chatId' => $chatId];
        $chatId = $this->dbQuery->queryPrepared($sql, $args)['creator_id'];

        return $chatId;
    }

    // Добавить сообщение
    public function add($message)
    {
        $userData = [
            'chat_message_chatid' => $message->chat,
            'chat_message_text' => $message->message,
            'chat_message_creatorid' => $message->author_id,
            'chat_message_time' => $message->time,
        ];
        $messageId = $this->dbQuery->insert('chat_message', $userData);

        return $messageId;
    }

    // Добавить пересылаемое сообщение
    public function addForwarded($message)
    {
        // добавить копию сообщения в указанный чат
        $messageId = $this->add($message);
        // установить флаг "пересылка сообщения"
        $fieldArray = ['chat_message_forward' => 1];
        $condition = [
            'condition_field_name' => 'chat_message_id',
            'condition_sign' => '=',
            'condition_field_value' => $messageId,
        ];
        $isUpdated = $this->dbQuery->update('chat_message', $fieldArray, $condition);

        return $isUpdated;
    }

    // изменить сообщение
    public function editMessage(string $msg, int $msgId)
    {
        // изменяем строку
        $this->dbQuery->exec("update chat_message set chat_message_text = '$msg' where chat_message_id = $msgId");
        // возвращаем строку
        $rslt = $this->dbQuery->queryPrepared('
            select chat_message_id as msg, 
                   chat_message_chatid as chat, 
                   chat_message_text as message, 
                   chat_message_time as time 
            from chat_message 
            where chat_message_id = :msgId
        ', ['msgId' => $msgId]);
        $rslt['messageType'] = 'EDIT';

        return $rslt;
    }

    // удалить сообщение
    public function removeMessage(int $msgId)
    {
        // получиим удаляемую строку
        $rslt = $this->dbQuery->queryPrepared('
            select chat_message_id as msg, chat_message_chatid as chat 
            from chat_message 
            where chat_message_id = :msgId
        ', ['msgId' => $msgId]);
        // удаляем
        $this->dbQuery->exec("delete from chat_message where chat_message_id = $msgId");
        // возвращаем информацию удаляемой строки
        $rslt['messageType'] = 'REMOVE';

        return $rslt;
    }

    // установить показ уведомлений чатов
    public function setNoticeShow($chatid, $userid, $notice)
    {
        $this->dbQuery->exec("
            update chat_participant 
            set chat_participant_isnotice = $notice 
            where chat_participant_chatid = $chatid 
              and chat_participant_userid = $userid
        ");

        $sql = '
            select chat_participant_isnotice 
            from chat_participant 
            where chat_participant_chatid = :chatid 
              and chat_participant_userid = :userid
        ';

        return $this->dbQuery->queryPrepared($sql, ['chatid' => $chatid, 'userid' => $userid])['chat_participant_isnotice'];
    }
}
