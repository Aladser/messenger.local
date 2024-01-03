<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы сообщений чатов */
class ChatEntity extends Model
{
    // получить ID диалога
    public function getDialogId($user1Id, $user2Id)
    {
        // поиск диалога пользователей
        $sql = '
            select chat_id from chat
            join chat_participant on chat_id = chat_participant_chatid
            where chat_participant_userid = :user1Id
            and chat_type = \'dialog\'
            and chat_id in (
	            select chat_id from chat
	            join chat_participant on chat_id = chat_participant_chatid
	            where chat_participant_userid = :user2Id
            );
        ';
        $query = $this->dbQuery->queryPrepared($sql, ['user1Id' => $user1Id, 'user2Id' => $user2Id]);

        // создание диалога, если не существует
        if (!$query) {
            return $this->dbQuery->executeProcedure("create_dialog($user1Id, $user2Id, @info)", '@info');
        }

        return intval($query['chat_id']);
    }

    /** получить ID группового чата*/
    public function getDiscussionId(string $groupName)
    {
        return $this->dbQuery->queryPrepared(
            'select chat_id from chat where chat_name = :groupName',
            ['groupName' => $groupName],
            false
        )[0]['chat_id'];
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
        $sql = 'select chat_participant_userid as recipient from chat_participant 
            where chat_participant_chatid = :chatId';
        $args = ['chatId' => $chatId];
        $queryResultData = $this->dbQuery->queryPrepared($sql, $args, false);
        $recipientIdArray = [];
        foreach ($queryResultData as $element) {
            array_push($recipientIdArray, $element['recipient']);
        }

        return $recipientIdArray;
    }

    /** удалить чат */
    public function removeChat($dialogId)
    {
        $result = $this->dbQuery->exec("delete from chat_participant where chat_participant_chatid  = $dialogId");
        $result += $this->dbQuery->exec("delete from chat_message where chat_message_chatid  = $dialogId");
        $result += $this->dbQuery->exec("delete from chat where chat_id = $dialogId");

        return $result;
    }

    // создать групповой чат
    public function createDiscussion(int $userHostId)
    {
        $groupId = $this->dbQuery->executeProcedure("create_discussion($userHostId, @info)", '@info');
        $sql = 'select chat_id as chat, chat_name as name from chat where chat_id = :groupId';

        return $this->dbQuery->queryPrepared($sql, ['groupId' => $groupId]);
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
        $this->dbQuery->exec("
            update chat_participant 
            set chat_participant_isnotice = $notice 
            where chat_participant_chatid = $chatid 
              and chat_participant_userid = $userid
        ");

        $sql = '
            select chat_participant_isnotice 
            from chat_participant 
            where chat_participant_chatid = :chatid 
              and chat_participant_userid = :userid
        ';

        return $this->dbQuery->queryPrepared($sql, ['chatid' => $chatid, 'userid' => $userid])['chat_participant_isnotice'];
    }
}
