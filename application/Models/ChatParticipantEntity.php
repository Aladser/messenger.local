<?php

namespace App\Models;

use App\Core\Model;

/** Участники чатов */
class ChatParticipantEntity extends Model
{
    // получить участников группы
    public function getGroupChatMembers($chatId): array
    {
        $sql = '
                select getPublicUserName(email, nickname, hide_email) as username 
                from chat_participants 
                join users on chat_participants.user_id = users.id
                where chat_id = :chatId
            ';
        $args = ['chatId' => $chatId];
        $groupChatMembers = $this->dbQuery->queryPrepared($sql, $args, false);

        return $groupChatMembers;
    }

    // добавить участника чата
    public function add($chatId, $userId): bool
    {
        $participantData = ['chat_id' => $chatId, 'user_id' => $userId];
        $isAdded = $this->dbQuery->insert('chat_participants', $participantData) > 0;

        return $isAdded;
    }

    // проверить существование пользователя в чате
    public function exists($chatId, $userId)
    {
        $sql = '
            select count(*) as count 
            from chat_participants 
            where chat_id = :chatId and user_id = :userId
        ';
        $args = ['chatId' => $chatId, 'userId' => $userId];
        $isExisted = $this->dbQuery->queryPrepared($sql, $args)['count'] > 0;

        return $isExisted;
    }

    // обновить показ уведомлений чатов
    public function updateNoticeShow(int $chatid, int $userid, int $notice): bool
    {
        $sql = 'update chat_participants set notice = :notice 
            where chat_id = :chatid and user_id = :userid';
        $args = ['notice' => $notice, 'chatid' => $chatid, 'userid' => $userid];
        $isUpdated = $this->dbQuery->updateDiffCondition($sql, $args);

        return $isUpdated;
    }
}
