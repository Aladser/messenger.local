<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы сообщений чатов */
class ChatEntity extends Model
{
    // получить ID персонального чата
    public function getPersonalChatId($user1Id, $user2Id)
    {
        $sql = "
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user1Id and type='personal'
            intersect
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user2Id and type='personal'
        ";
        $args = ['user1Id' => $user1Id, 'user2Id' => $user2Id];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['chat_id'] : false;
    }

    /** получить ID группового чата*/
    public function getGroupChatId(string $groupName)
    {
        $sql = 'select id from chats where name = :groupName';
        $args = ['groupName' => $groupName];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['id'] : false;
    }

    // получить имя чата
    // используется при создании группового чата
    public function getName(int $id)
    {
        $sql = 'select name from chats where id = :id';
        $args = ['id' => $id];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['name'] : false;
    }

    // добавить
    public function add(string $type, int $creatorId)
    {
        $chatData = [
            'type' => $type,
            'creator_id' => $creatorId,
        ];

        if ($type === 'group') {
            $sql = 'select max(id) as max_id from chats';
            $index = $this->dbQuery->query($sql)['max_id'];
            $chatData['name'] = 'Группа '.($index + 1);
        }

        $chatId = $this->dbQuery->insert('chats', $chatData);

        return $chatId;
    }

    // удалить
    public function remove($chatId)
    {
        $this->dbQuery->exec("delete from chat_participants where chat_id = $chatId");
        $this->dbQuery->exec("delete from messages where chat_id = $chatId");
        $isDeleted = $this->dbQuery->exec("delete from chats where id = $chatId");

        return $isDeleted > 0;
    }

    // получить личные чаты пользователя
    public function getUserPersonalChats(int $userId, bool $onlyId = false): array
    {
        $sql = "
                select chat_id as chat, user_id as user, photo,
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
                where type = 'personal'
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
                $personalChatIDList[] = $user['user'];
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

    // возвращает групповые чаты пользователя
    public function getDiscussions(int $userId)
    {
        $sql = "
            select chat_id as chat, name, notice       
            from chat_participants
            join chats on chat_participants.chat_id = chats.id
            where type = 'group' and user_id = :userId
        ";
        $args = ['userId' => $userId];

        return $this->dbQuery->queryPrepared($sql, $args, false);
    }

    // возвращает создателя группового чата
    public function getDiscussionCreatorId($chatId)
    {
        $sql = 'select creator_id from chats where id = :chatId';
        $args = ['chatId' => $chatId];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['creator_id'] : false;
    }

    // установить показ уведомлений чатов
    public function setNoticeShow($chatid, $userid, $notice)
    {
        $sql = "update chat_participants set notice = $notice 
        where chat_id = :chatid and user_id = :userid";
        $args = ['chatid' => $chatid, 'userid' => $userid];
        $this->dbQuery->queryPrepared($sql, $args);

        $sql = 'select notice from chat_participants 
        where chat_id = :chatid and user_id = :userid';
        $args = ['chatid' => $chatid, 'userid' => $userid];
        $notice = $this->dbQuery->queryPrepared($sql, $args)['notice'];

        return $notice;
    }
}
