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
            'chat_id' => $message->chat,
            'content' => $message->message,
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

    // установить показ уведомлений чатов
    public function setNoticeShow($chatid, $userid, $notice)
    {
        $sql = "update chat_participants set notice = $notice 
        where chat_id = $chatid and user_id = $userid";
        $this->dbQuery->exec($sql);

        $sql = 'select notice from chat_participants 
            wherechat_id = :chatid and user_id = :userid';
        $args = ['chatid' => $chatid, 'userid' => $userid];
        $notice = $this->dbQuery->queryPrepared($sql, $args)['chat_participant_isnotice'];

        return $notice;
    }
}
