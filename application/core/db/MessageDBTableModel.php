<?php

namespace core\db;

/**
 * класс БД таблицы сообщений чатов
*/
class MessageDBTableModel extends DBTableModel{
    // получить ID диалога
    public function getDialogId($user1Id, $user2Id){
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
            $chatId = $this->db->executeProcedure("create_chat($user1Id, $user2Id, @info)", '@info');
            return $chatId;
        }
        return $query['chat_id'];
    }

    public function addMessage($msg){
        $userId = $this->db->query("select user_id from users where user_email='$msg->fromuser' or user_nickname='$msg->fromuser'")['user_id'];
        $sql = "insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time) values($msg->idChat, '$msg->message', $userId, '$msg->time')";
        return $this->db->exec($sql);
    }

    public function getMessages(int $chatId){
        $sql = "
            select chat_message_id as id, 
            chat_message_chatid as chatId, 
            getPublicUserName(user_email, user_nickname, user_hide_email) as fromuser, 
            chat_message_text as message, 
            chat_message_time as time
            from chat_message join users on user_id = chat_message_creatorid
            where chat_message_chatid = $chatId
        ";
        return $this->db->query($sql, false);
    }
}