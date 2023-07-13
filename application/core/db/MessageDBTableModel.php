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
            $this->db->exec("call create_chat($user1Id, $user2Id, @info)");
            $query = $this->db->query($sql);
        }
        return ['chatId'=>$query['chat_id'], 'chat'=>1];
    }

    public function addMessage($message){
        
    }
}