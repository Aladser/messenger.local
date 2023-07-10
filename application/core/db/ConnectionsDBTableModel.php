<?php

namespace core\db;

/*
 * класс БД таблицы соединений вебсокета
*/
class ConnectionsDBTableModel extends DBTableModel{
    /*
        сохранить подключение в БД
        возвращает:
        0 - ошибка добавления
        1 - добавлено соединение
        2 - соединение существует
        -1 - пользователь не существует (возможно, подмена)
    */
    public function addConnection($data){
        $connection_ws_id = intval($data['userId']);
        $user_email = trim($data['author']);
        // поиск пользователя в БД
        $isUser = $this->db->query("select count(*) as count from users where user_email = '{$user_email}'")['count'] > 0;
        if($isUser){
            $isConnection = $this->db->query("select count(*) as count from connections where connection_public_username = '$user_email'")['count'] > 0;
            if(!$isConnection){
                $sql = "insert connections(connection_ws_id, connection_public_username) values($connection_ws_id, '$user_email')";
                return $this->db->exec($sql)== 1 ? "CONNECTION $user_email ESTABILISHED" : "CONNECTION $user_email ERROR";
            }
            else{
                return "CONNECTION $user_email ALREADY EXISTS";
            }
        }
        else{
            return "USER $user_email NO EXISTS";
        }
    }

    // получить публичное имя пользователя соединения
    public function getConnectionUserEmail($connId){
        $sql = "select connection_public_username as conn_email from connections where connection_ws_id = $connId";
        return $this->db->query($sql)['conn_email'];
    }

    // удаление закрытого соединения
    public function removeConnection($connId){
        return $this->db->exec("delete from connections where connection_ws_id = $connId");
    }

    // очистка таблицы соединений
    public function clearConnections(){
        $this->db->exec('delete from connections');
        $this->db->exec('alter table connections AUTO_INCREMENT = 1');
    }
}