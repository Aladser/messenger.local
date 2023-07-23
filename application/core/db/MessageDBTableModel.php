<?php
namespace core\db;

/** класс БД таблицы сообщений чатов */
class MessageDBTableModel extends DBTableModel
{
    // получить ID диалога
    public function getDialogId($user1Id, $user2Id)
    {
        // поиск диалога пользователей
        $sql = "
            select chat_id from extended_chat 
            where chat_participant_userid = $user1Id 
            and chat_type = 'dialog'
            and chat_id in (select chat_id from extended_chat where chat_participant_userid = $user2Id);
        ";
        $query = $this->db->query($sql);

        // создание диалога, если не существует
        if(!$query){
            $chatId = $this->db->executeProcedure("create_dialog($user1Id, $user2Id, @info)", '@info');
            return $chatId;
        }
        return intval($query['chat_id']);
    }

    // создать групповой чат
    public function createDiscussion(int $userHostId)
    {
        $groupId = $this->db->executeProcedure("create_discussion($userHostId, @info)", '@info');
        $sql = "select chat_id, chat_name from chat where chat_id = $groupId";
        return $this->db->query($sql);
    }

    // возвращает групповые чаты пользователя
    public function getDiscussions(int $userHostId)
    {
        $sql = "
        select chat_id, chat_name, chat_participant_isnotice as chat_isnotice       
        from chat_participant
        join chat on chat_participant.chat_participant_chatid = chat.chat_id
        where chat_type = 'discussion' and chat_participant_userid = $userHostId
        ";
        return $this->db->query($sql, false);
    }

    // возвращает создателя группового чата
    public function getDiscussionCreatorId($chatId)
    {
        $sql = "select chat_creatorid from chat where chat_id = $chatId";
        return $this->db->query($sql)['chat_creatorid'];
    }

    // добавить сообщение
    public function addMessage($msg)
    {
        $out = '@chatid';
        $func = "add_message($msg->chatId, '$msg->message', '$msg->fromuser', '$msg->time', $out)";
        return $this->db->executeProcedure($func, $out);
    }

    // добавить пересылаемое сообщение
    public function addForwardedMessage($msg){
        $out = '@chatid';
        $func = "add_forwarded_message($msg->fromuserId, $msg->msgId, $msg->chatId, '$msg->time', $out)";
        return $this->db->executeProcedure($func, $out);
    }

    // изменить сообщение
    public function editMessage(string $msg, int $msgId)
    {
        // изменяем строку
        $this->db->exec("update chat_message set chat_message_text = '$msg' where chat_message_id = $msgId");
        // возвращаем строку
        $rslt = $this->db->query("
            select chat_message_id, chat_message_chatid as chatId, chat_message_text, chat_message_time 
            from chat_message 
            where chat_message_id = $msgId
        ");
        $rslt['messageType'] = 'EDIT';
        return $rslt;
    }

    // удалить сообщение
    public function removeMessage(int $msgId)
    {
        // получиим удаляемую строку
        $rslt = $this->db->query("
            select chat_message_id, chat_message_chatid as chatId 
            from chat_message 
            where chat_message_id = $msgId
        ");
        // удаляем
        $this->db->exec("delete from chat_message where chat_message_id = $msgId");
        // возвращаем информацию удаляемой строки
        $rslt['messageType'] = 'REMOVE';
        return $rslt;
    }

    // возвращает сообшения диалога
    public function getMessages(int $chatId)
    {
        $sql = "
            select chat_message_id, 
            chat_message_chatid as chatId, 
            getPublicUserName(user_email, user_nickname, user_hide_email) as fromuser, 
            chat_message_text as message, 
            chat_message_time as time,
            chat_message_forward as forward
            from chat_message join users on user_id = chat_message_creatorid
            where chat_message_chatid = $chatId
        ";
        return $this->db->query($sql, false);
    }

    // установить показ уведомлений чатов
    public function setNoticeShow($chatid, $userid, $notice)
    {
        $this->db->exec("
            update chat_participant 
            set chat_participant_isnotice = $notice where chat_participant_chatid = $chatid and chat_participant_userid = $userid
        ");
        $sql = "select chat_participant_isnotice from chat_participant where chat_participant_chatid = $chatid and chat_participant_userid = $userid";
        return $this->db->query($sql)['chat_participant_isnotice'];
    }
}
