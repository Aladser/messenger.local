<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы сообщений чатов */
class ChatEntity extends Model
{
    // получить ID диалога
    public function getDialogId($user1Id, $user2Id)
    {
        $sql = "
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user1Id and type='dialog'
            intersect
            select chat_id from chat_participants 
            join chats on chats.id = chat_participants.chat_id
            where user_id = :user2Id and type='dialog'
        ";
        $args = ['user1Id' => $user1Id, 'user2Id' => $user2Id];
        $chatId = $this->dbQuery->queryPrepared($sql, $args)['chat_id'];

        return $chatId;
    }

    /** получить ID группового чата*/
    public function getDiscussionId(string $groupName)
    {
        $sql = 'select id from chats where name = :groupName';
        $args = ['groupName' => $groupName];
        $id = $this->dbQuery->queryPrepared($sql, $args)['id'];

        return $id;
    }

    public function add(string $type, int $creatorId)
    {
        $chatData = [
            'type' => $type,
            'creator_id' => $creatorId,
        ];

        if ($type === 'discussion') {
            $sql = 'select max(id) as max_id from chats';
            $index = $this->dbQuery->query($sql)['max_id'];
            $chatData['name'] = 'Группа '.($index + 1);
        }

        $chatId = $this->dbQuery->insert('chats', $chatData);

        return $chatId;
    }

    // получить имя чата
    // используется при создании группового чата
    public function getName(int $id)
    {
        $sql = 'select name from chats where id = :id';
        $args = ['id' => $id];
        $name = $this->dbQuery->queryPrepared($sql, $args)['name'];

        return $name;
    }

    // возвращает групповые чаты пользователя
    public function getDiscussions(int $userId)
    {
        $sql = "
            select chat_id as chat, name, notice       
            from chat_participants
            join chats on chat_participants.chat_id = chats.id
            where type = 'discussion' and user_id = :userId
        ";
        $args = ['userId' => $userId];

        return $this->dbQuery->queryPrepared($sql, $args, false);
    }

    // id получателей сообщения
    public function getChatParticipantIds($chatId)
    {
        $sql = 'select user_id as recipient from chat_participants 
            where chat_id = :chatId';
        $args = ['chatId' => $chatId];
        $queryResultData = $this->dbQuery->queryPrepared($sql, $args, false);
        $recipientIdArray = [];
        foreach ($queryResultData as $element) {
            array_push($recipientIdArray, $element['recipient']);
        }

        return $recipientIdArray;
    }

    /** удалить чат */
    public function remove($chatId)
    {
        $this->dbQuery->exec("delete from chat_participants where chat_id = $chatId");
        $this->dbQuery->exec("delete from messages where chat_id = $chatId");
        $isDeleted = $this->dbQuery->exec("delete from chats where id = $chatId");

        return $isDeleted > 0;
    }

    // возвращает создателя группового чата
    public function getDiscussionCreatorId($chatId)
    {
        $sql = 'select creator_id from chats where id = :chatId';

        return $this->dbQuery->queryPrepared($sql, ['chatId' => $chatId])['creator_id'];
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
