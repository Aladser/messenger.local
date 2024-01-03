<?php

namespace App\Models;

use App\Core\Model;

/** Групповой контакт */
class GroupContactEntity extends Model
{
    public function get($groupId)
    {
        $sql = '
                select user_id, getPublicUserName(email, nickname, hide_email) as publicname 
                from chat_participants 
                join users on chat_participants.user_id = users.id
                where chat_id = :groupId
            ';
        $args = ['groupId' => $groupId];
        $participants = $this->dbQuery->queryPrepared($sql, $args, false);

        return $participants;
    }

    public function add($chatId, $userId)
    {
        $sql = "insert into chat_participants(chat_participant_chatid, chat_participant_userid)
        values ($chatId, $userId)";

        return $this->dbQuery->exec($sql);
    }

    public function exists($chatId, $userId)
    {
        $sql = 'select count(*) as count from chat_participants where chat_participant_chatid = :chatId and chat_participant_userid = :userId';
        $args = ['chatId' => $chatId, 'userId' => $userId];
        $isExisted = $this->dbQuery->queryPrepared($sql, $args)['count'] > 0;

        return $isExisted;
    }
}
