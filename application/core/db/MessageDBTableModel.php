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
        /*
            'message':
            'fromuser': 
            'touser': 
            'idChat':
            'time': 
        */
        //$this->db->exec("insert into chat_message(chat_message_chatid, chat_message_text, chat_message_creatorid, chat_message_time) values($msg, $msg, $msg, $msg)");
    }
}