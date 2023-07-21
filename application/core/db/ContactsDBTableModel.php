<?php

namespace core\db;

/** класс БД таблицы контактов пользователей, контактов групповых чатов */
class ContactsDBTableModel extends DBTableModel{
    // добавить контакт
    function addContact($contactId, $userId){
        return $this->db->exec("insert into contacts(cnt_user_id, cnt_contact_id) values($userId, $contactId)");
    }

    function existsContact($contactId, $userId){
        return $this->db->query("select * from contacts where cnt_user_id = $userId and cnt_contact_id = $contactId or cnt_user_id = $contactId and cnt_contact_id = $userId");
    }

    // добавить участника группового чата
    function addGroupContact($chatId, $userId){
        $isContact = $this->db->query("select * from chat_participant where chat_participant_chatid = $chatId and chat_participant_userid = $userId");
        return $isContact ? 1 :  $this->db->exec("insert into chat_participant(chat_participant_chatid, chat_participant_userid) values ($chatId, $userId)");
    }

    function getContact($userId, $contactId){
        $sql = "
            select chat_id, user_id, user_photo,
            getPublicUserName(user_email, user_nickname, user_hide_email) as username, 
            (select chat_participant_isnotice from chat_participant where chat_participant_chatid = chat_id and chat_participant_userid = $userId) as isnotice
            from chat 
            join chat_participant on chat_participant_chatid = chat_id
            join users on chat_participant_userid = user_id
            where chat_type = 'dialog'
            and chat_id in (select chat_participant_chatid from chat_participant where chat_participant_userid = $userId)
            and user_id = $contactId;
        ";
        return $this->db->query($sql, false);
    }

    // получить контакты пользователя
    function getContacts($userId){
        $sql = "
            select chat_id, user_id, user_photo,
            getPublicUserName(user_email, user_nickname, user_hide_email) as username, 
            (select chat_participant_isnotice from chat_participant where chat_participant_chatid = chat_id and chat_participant_userid = $userId) as isnotice
            from chat 
            join chat_participant on chat_participant_chatid = chat_id
            join users on chat_participant_userid = user_id
            where chat_type = 'dialog'
            and chat_id in (select chat_participant_chatid from chat_participant where chat_participant_userid = $userId)
            and user_id != $userId
        ";
        return $this->db->query($sql, false);
    }

    // получить участников группового чата
    function getGroupContacts($groupId){
        $sql = "
        select user_id, getPublicUserName(user_email, user_nickname, user_hide_email) as publicname from chat_participant 
        join users on chat_participant.chat_participant_userid = users.user_id
        where chat_participant_chatid = $groupId
        ";
        return $this->db->query($sql, false);
    }
}