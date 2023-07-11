<?php

namespace core\db;

/**
 * класс БД таблицы соединений вебсокета
*/
class ConnectionsDBTableModel extends DBTableModel{
    /**
     * сохранить подключение в БД
    */
    public function addConnection($data){
        $connection_ws_id = intval($data['userId']);
        $user_email = trim($data['author']);
        // поиск пользователя в БД
        $user = $this->db->query("select user_nickname, user_hide_email from users where user_email = '$user_email'");
        if($user){
            // поиск соединения в БД
            $userNickname = $user['user_nickname'];
            $isConnection = $this->db->query("select count(*) as count from connections where connection_public_username = '$user_email' or connection_public_username = '$userNickname'")['count'] > 0;
            // публичное имя пользователя
            $publicUsername = $user['user_hide_email']==="1" ? $user['user_nickname'] : $user_email;
            
            if(!$isConnection){
                $sqlRslt = $this->db->exec("insert connections(connection_ws_id, connection_public_username) values($connection_ws_id, '$publicUsername')");
                // при добавлении соединения возвращается публичное имя пользователя или ошибка добавления
                return $sqlRslt == 1 ? ['publicUsername' => $publicUsername] : ['systeminfo' => "$user_email: DATABASE ERROR"];
            }
            else{
                return ['publicUsername' => $publicUsername]; // соединение уже есть в БД. Возвращается публичное имя пользователя
            }
        }
        else{
            return ['systeminfo' => "USER $user_email NO EXISTS"]; // пользователь в БД не существует
        }
    }

    /**
     * получить публичное имя пользователя соединения
    */ 
    public function getConnectionPublicUsername($connId){
        $sql = "select connection_public_username from connections where connection_ws_id = $connId";
        return $this->db->query($sql)['connection_public_username'];
    }

    /**
     * удалить закрытое соединение из БД
    */ 
    public function removeConnection($connId){
        return $this->db->exec("delete from connections where connection_ws_id = $connId");
    }

    /**
     * очистить таблицу соединений
    */ 
    public function removeConnections(){
        $this->db->exec('delete from connections');
        $this->db->exec('alter table connections AUTO_INCREMENT = 1');
    }
}