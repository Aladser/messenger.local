<?php

namespace Aladser\Models;

/** класс БД таблицы сообщений чатов */
class MessageDBTableModel extends DBTableModel
{
    // возвращает сообшения диалога
    public function getMessages(int $chatId)
    {
        $sql = '
            select chat_message_id as msg, 
            chat_message_chatid as chat, 
            getPublicUserName(user_email, user_nickname, user_hide_email) as author, 
            chat_message_text as message, 
            chat_message_time as time,
            chat_message_forward as forward
            from chat_message join users on user_id = chat_message_creatorid
            where chat_message_chatid = :chatId
        ';
        return $this->db->queryPrepared($sql, ['chatId' => $chatId], false);
    }

    // получить ID диалога
    public function getDialogId($user1Id, $user2Id)
    {
        // поиск диалога пользователей
        $sql = '
            select chat_id from chat
            join chat_participant on chat_id = chat_participant_chatid
            where chat_participant_userid = :user1Id
            and chat_type = \'dialog\'
            and chat_id in (
	            select chat_id from chat
	            join chat_participant on chat_id = chat_participant_chatid
	            where chat_participant_userid = :user2Id
            );
        ';
        $query = $this->db->queryPrepared($sql, ['user1Id' => $user1Id, 'user2Id' => $user2Id]);

        // создание диалога, если не существует
        if (!$query) {
            return $this->db->executeProcedure("create_dialog($user1Id, $user2Id, @info)", '@info');
        }
        return intval($query['chat_id']);
    }

    /** получить ID группового чата*/
    public function getDiscussionId(string $groupName)
    {
        return $this->db->queryPrepared(
            'select chat_id from chat where chat_name = :groupName',
            ['groupName' => $groupName],
            false
        )[0]['chat_id'];
    }

    /** удалить чат */
    public function removeChat($dialogId)
    {
        $result = $this->db->exec("delete from chat_participant where chat_participant_chatid  = $dialogId");
        $result += $this->db->exec("delete from chat_message where chat_message_chatid  = $dialogId");
        $result += $this->db->exec("delete from chat where chat_id = $dialogId");
        return $result;
    }

    // создать групповой чат
    public function createDiscussion(int $userHostId)
    {
        $groupId = $this->db->executeProcedure("create_discussion($userHostId, @info)", '@info');
        $sql = 'select chat_id as chat, chat_name as name from chat where chat_id = :groupId';
        return $this->db->queryPrepared($sql, ['groupId' => $groupId]);
    }


    // возвращает групповые чаты пользователя
    public function getDiscussions(int $userHostId)
    {
        $sql = '
        select chat_id as chat, chat_name as name, chat_participant_isnotice as notice       
        from chat_participant
        join chat on chat_participant.chat_participant_chatid = chat.chat_id
        where chat_type = \'discussion\' and chat_participant_userid = :userHostId
        ';
        return $this->db->queryPrepared($sql, ['userHostId' => $userHostId], false);
    }

    // возвращает создателя группового чата
    public function getDiscussionCreatorId($chatId)
    {
        $sql = 'select chat_creatorid from chat where chat_id = :chatId';
        return $this->db->queryPrepared($sql, ['chatId' => $chatId])['chat_creatorid'];
    }

    // добавить сообщение
    public function addMessage($msg)
    {
        $out = '@chatid';
        $func = "add_message($msg->chat, '$msg->message', '$msg->author', '$msg->time', $out)";
        return $this->db->executeProcedure($func, $out);
    }

    // добавить пересылаемое сообщение
    public function addForwardedMessage($msg)
    {
        $out = '@chatid';
        $func = "add_forwarded_message($msg->authorId, $msg->msgId, $msg->chat, '$msg->time', $out)";
        return $this->db->executeProcedure($func, $out);
    }

    // изменить сообщение
    public function editMessage(string $msg, int $msgId)
    {
        // изменяем строку
        $this->db->exec("update chat_message set chat_message_text = '$msg' where chat_message_id = $msgId");
        // возвращаем строку
        $rslt = $this->db->queryPrepared('
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
        $rslt = $this->db->queryPrepared('
            select chat_message_id as msg, chat_message_chatid as chat 
            from chat_message 
            where chat_message_id = :msgId
        ', ['msgId' => $msgId]);
        // удаляем
        $this->db->exec("delete from chat_message where chat_message_id = $msgId");
        // возвращаем информацию удаляемой строки
        $rslt['messageType'] = 'REMOVE';
        return $rslt;
    }

    // установить показ уведомлений чатов
    public function setNoticeShow($chatid, $userid, $notice)
    {
        $this->db->exec("
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
        return $this->db->queryPrepared($sql, ['chatid' => $chatid, 'userid' => $userid])['chat_participant_isnotice'];
    }
}
