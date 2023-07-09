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
        $connection_user_email = trim($data['author']);
        // поиск пользователя в БД
        $isUser = $this->db->query("select count(*) as count from users where user_email = '{$connection_user_email}'")['count'] > 0;
        if($isUser){
            $isConnection = $this->db->query("select count(*) as count from connections where connection_user_email = '$connection_user_email'")['count'] > 0;
            if(!$isConnection){
                $sql = "insert connections(connection_ws_id, connection_user_email) values($connection_ws_id, '$connection_user_email')";
                return $this->db->exec($sql);
            }
            else{
                return 2;
            }
        }
        else{
            return -1;
        }
    }

    // получить почту пользователя соединения
    public function getConnectionUserEmail($connId){
        $sql = "select connection_user_email as conn_email from connections where connection_ws_id = $connId";
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