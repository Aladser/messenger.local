<?php

namespace Aladser\Models;

/** класс БД таблицы контактов пользователей, контактов групповых чатов */
class ContactsDBTableModel extends DBTableModel
{
    // добавить контакт
    public function addContact($contactId, $userId)
    {
        return $this->db->exec("insert into contacts(cnt_user_id, cnt_contact_id) values($userId, $contactId)");
    }

    /** удалить контакт */
    public function removeContact($contactId, $userId)
    {
        $sql = "
            delete from contacts 
            where (cnt_user_id=$userId and cnt_contact_id=$contactId) 
            or (cnt_contact_id=$userId and cnt_user_id=$contactId)
        ";
        return $this->db->exec($sql);
    }

    // проверка существования
    public function existsContact($contactId, $userId)
    {
        return $this->db->queryPrepared(
            'select * 
            from contacts 
            where cnt_user_id = :userId and cnt_contact_id = :contactId 
            or cnt_user_id = :contactId and cnt_contact_id = :userId',
            ['userId' => $userId, 'contactId' => $contactId]
        );
    }

    // получить контакт пользователя
    public function getContact($userId, $contactId)
    {
        $sql = '
            select chat_id, user_id, user_photo,
            getPublicUserName(user_email, user_nickname, user_hide_email) as username, 
            (
                select chat_participant_isnotice
                from chat_participant
                where chat_participant_chatid = chat_id 
                and chat_participant_userid = :userId
            ) as isnotice
            from chat 
            join chat_participant on chat_participant_chatid = chat_id
            join users on chat_participant_userid = user_id
            where chat_type = \'dialog\'
            and chat_id in (
                select chat_participant_chatid 
                from chat_participant 
                where chat_participant_userid = :userId
            )
            and user_id = :contactId
        ';
        return $this->db->queryPrepared($sql, ['userId' => $userId, 'contactId' => $contactId], false);
    }

    // получить контакты пользователя
    public function getContacts($userId)
    {
        $sql = '
            select chat_id as chat, user_id as user, user_photo as photo,
            getPublicUserName(user_email, user_nickname, user_hide_email) as name, 
            (
                select chat_participant_isnotice 
                from chat_participant 
                where chat_participant_chatid = chat_id 
                  and chat_participant_userid = :userId
            ) as notice
            from chat 
            join chat_participant on chat_participant_chatid = chat_id
            join users on chat_participant_userid = user_id
            where chat_type = \'dialog\'
            and chat_id in (
                select chat_participant_chatid
                from chat_participant
                where chat_participant_userid = :userId
            )
            and user_id != :userId
        ';
        return $this->db->queryPrepared($sql, ['userId' => $userId], false);
    }

    /**
     * добавить участника группового чата
     */
    public function addGroupContact($chatId, $userId)
    {
        $isContact = $this->db->queryPrepared('
            select * from chat_participant where chat_participant_chatid = :chatId and chat_participant_userid = :userId
        ', ['chatId' => $chatId, 'userId' => $userId]);
        if ($isContact) {
            return 1;
        } else {
            $sql = "
                insert into chat_participant(chat_participant_chatid, chat_participant_userid)
                values ($chatId, $userId)
            ";
            return $this->db->exec($sql);
        }
    }

    // получить участников группового чата
    public function getGroupContacts($groupId)
    {
        $sql = '
            select user_id, getPublicUserName(user_email, user_nickname, user_hide_email) as publicname 
            from chat_participant 
            join users on chat_participant.chat_participant_userid = users.user_id
            where chat_participant_chatid = :groupId
        ';
        return $this->db->queryPrepared($sql, ['groupId' => $groupId], false);
    }
}
