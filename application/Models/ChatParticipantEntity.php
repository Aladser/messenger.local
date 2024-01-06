<?php

namespace App\Models;

use App\Core\Model;

/** Участники чатов */
class ChatParticipantEntity extends Model
{
    // получить личные чаты пользователя
    public function getUserChatMembers(int $userId, bool $onlyId = false): array
    {
        $sql = "
            select chat_id as chat, user_id as user, photo as photo,
            getPublicUserName(email, nickname, hide_email) as username, 
            (
                select notice 
                from chat_participants 
                where chat_id = chats.id 
                and user_id = :userId
            ) as notice
            from chats 
            join chat_participants on chat_id = chats.id
            join users on user_id = users.id
            where type = 'dialog'
            and chat_id in (
                select chat_id
                from chat_participants
                where user_id = :userId)
            and user_id != :userId";
        $args = ['userId' => $userId];
        $personalChatList = $this->dbQuery->queryPrepared($sql, $args, false);

        if ($onlyId) {
            // только ID
            $personalChatIDList = [];
            foreach ($personalChatList as $user) {
                $personalChatIDList[] = $user['user_id'];
            }

            return $personalChatIDList;
        } else {
            // полные данные
            $cleanedPersonalChatList = [];
            // удаление дублей
            foreach ($personalChatList as $user) {
                $cleanedPersonalChatList[] = [
                    'chat' => $user['chat'],
                    'user' => $user['user'],
                    'photo' => $user['photo'],
                    'username' => $user['username'],
                    'notice' => $user['notice'],
                ];
            }

            return $cleanedPersonalChatList;
        }
    }

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
}
