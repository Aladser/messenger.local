<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы сообщений чатов */
class ChatEntity extends Model
{
    /** получить ID персонального чата.
     *
     * @param int $user1Id id пользователя
     * @param int $user2Id id пользователя
     *
     * @return int id чата
     */
    public function getPersonalChatId(int $user1Id, int $user2Id): int
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

    /** получить ID группового чата.
     *
     * @param string $groupName имя чата
     *
     * @return int id чата
     */
    public function getGroupChatId(string $groupName): int
    {
        $sql = 'select id from chats where name = :groupName';
        $args = ['groupName' => $groupName];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['id'] : false;
    }

    /** получить имя чата.
     *
     * используется при создании группового чата
     *
     * @param int $id id
     *
     * @return string имя
     */
    public function getName(int $id): string
    {
        $sql = 'select name from chats where id = :id';
        $args = ['id' => $id];
        $queryResult = $this->dbQuery->queryPrepared($sql, $args);

        return $queryResult ? $queryResult['name'] : false;
    }

    /** получить личные чаты пользователя.
     *
     * @param int  $userId id чата
     * @param bool $onlyId только id?
     *
     * @return array [
     *               chat - id чата,
     *               user - id контакта,
     *               photo - фото контакта,
     *               username - публичное имя контакта,
     *               notice - уведомления этого контакта
     *               ]
     */
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

    /** получить групповые чаты пользователя.
     *
     * @param int $userId id пользователя
     *
     * @return array [
     *               chat id чата
     *               name имя чата
     *               notice уведомления чата
     *               ]
     */
    public function getUserGroupChats(int $userId): array
    {
        $sql = "
            select chat_id as chat, name, notice       
            from chat_participants
            join chats on chat_participants.chat_id = chats.id
            where type = 'group' and user_id = :userId
        ";
        $args = ['userId' => $userId];
        $chatList = $this->dbQuery->queryPrepared($sql, $args, false);

        return $chatList;
    }

    /** добавить.
     *
     * @param string $type      тип чата : personal, group
     * @param int    $creatorId создатель чата
     *
     * @return void id чата
     */
    public function add(string $type, int $creatorId): int
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

    /** удалить.
     *
     * @param int $chatId id чата
     *
     * @return bool удален?
     */
    public function remove(int $chatId): bool
    {
        $whereCondition = 'id = :id';
        $args = ['id' => $chatId];
        $isDeleted = $this->dbQuery->delete('chats', $whereCondition, $args) > 0;

        return $isDeleted;
    }
}
