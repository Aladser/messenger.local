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
            return ['chatId'=>$chatId, 'chat'=>1];
        }

        return ['chatId'=>$query['chat_id'], 'chat'=>1];
    }

    public function addMessage($msg){
        $userId = $this->db->query("select user_id from users where user_email='$msg->fromuser' or user_nickname='$msg->fromuser'")['user_id'];
        $sql = "insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time) values($msg->idChat, '$msg->message', $userId, '$msg->time')";
        return $this->db->exec($sql);
    }

    public function getMessages(){
        
    }
}