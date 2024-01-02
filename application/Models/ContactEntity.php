<?php

namespace App\Models;

use App\Core\Model;

/** Контакт пользователя */
class ContactEntity extends Model
{
    public function get($userId, $contactId)
    {
        $sql = "
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
                where chat_type = 'dialog'
                and chat_id in (
                    select chat_participant_chatid 
                    from chat_participant 
                    where chat_participant_userid = :userId
                )
                and user_id = :contactId
            ";
        $args = ['userId' => $userId, 'contactId' => $contactId];
        $contact = $this->dbQuery->queryPrepared($sql, $args, false);

        return $contact;
    }

    public function getUserContacts(int $userId, bool $onlyId = false): array
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
        $args = ['userId' => $userId];

        $contactList = $this->dbQuery->queryPrepared($sql, $args, false);
        if ($onlyId) {
            $contactIdList = [];
            foreach ($contactList as $contact) {
                $contactIdList[] = $contact['user'];
            }

            return $contactIdList;
        } else {
            return $contactList;
        }
    }

    public function add($contactId, $userId)
    {
        $fields = ['cnt_user_id' => $userId, 'cnt_contact_id' => $contactId];
        $userId = $this->dbQuery->insert('contacts', $fields);

        return $userId > 0;
    }

    public function remove($contactId, $userId)
    {
        $whereCondition = '(cnt_user_id=:userId and cnt_contact_id=:contactId) 
        or (cnt_contact_id=:userId and cnt_user_id=:contactId)';
        $args = ['userId' => $userId, 'contactId' => $contactId];
        $isRemoved = $this->dbQuery->delete('contacts', $whereCondition, $args);

        return $isRemoved;
    }

    public function exists($contactId, $userId)
    {
        $sql = 'select count(*) as count from contacts 
        where cnt_user_id = :userId and cnt_contact_id = :contactId 
        or cnt_user_id = :contactId and cnt_contact_id = :userId';
        $args = ['userId' => $userId, 'contactId' => $contactId];
        $isExisted = $this->dbQuery->queryPrepared($sql, $args)['count'] > 0;

        return $isExisted;
    }
}
