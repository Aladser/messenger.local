<?php

namespace App\Models;

use App\Core\Model;

/** Класс БД таблицы соединений вебсокета */
class ConnectionEntity extends Model
{
    public function getUserId()
    {
    }

    // получить публичное имя пользователя соединения
    public function getConnectionPublicUsername(int $connId)
    {
        $sql = '
                select getPublicUserName(user_email, user_nickname, user_hide_email) as username 
                from users where user_id = (select connection_userid from connections 
                where connection_ws_id = :connId)
            ';
        $query = $this->dbQuery->queryPrepared($sql, ['connId' => $connId]);

        return $query['username'] ?? '';
    }

    // сохранить подключение в БД
    public function addConnection(array $data): array
    {
        $connection_ws_id = intval($data['wsId']);
        $user_email = trim($data['author']);

        $user = $this->dbQuery->queryPrepared('
            select user_id, getPublicUserName(user_email, user_nickname, user_hide_email) as publicusername 
            from users 
            where user_email = :email
        ', ['email' => $user_email]);

        if ($user) {
            // поиск соединения в БД
            $userId = $user['user_id'];
            $isConnection = $this->dbQuery->queryPrepared(
                'select * from connections where connection_userid = :id',
                ['id' => $userId]
            );
            // не могу понять откуда берется нулевой connId из Ratchet. Удаляю его
            if (!$isConnection && $connection_ws_id != 0) {
                $sqlRslt = $this->dbQuery->exec("
                    insert connections(connection_ws_id, connection_userid) values($connection_ws_id, $userId)
                ");

                // при добавлении соединения возвращается публичное имя пользователя или ошибка добавления
                return $sqlRslt == 1
                    ? ['publicUsername' => $user['publicusername']]
                    : ['systeminfo' => "$user_email: DATABASE ERROR"];
            } else {
                // соединение уже есть в БД. Возвращается публичное имя пользователя
                return ['publicUsername' => $user['publicusername']];
            }
        } else {
            return ['systeminfo' => "USER $user_email NO EXISTS"]; // пользователь в БД не существует
        }
    }

    // удалить закрытое соединение из БД
    public function removeConnection(int $connId)
    {
        return $this->dbQuery->exec("delete from connections where connection_ws_id = $connId");
    }

    // очистить таблицу соединений
    public function removeConnections()
    {
        $this->dbQuery->exec('delete from connections');
    }
}
