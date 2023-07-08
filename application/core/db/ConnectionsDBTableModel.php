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
    */
    public function addConnection($data){
        $isConnection = $this->db->query("select count(*) as count from connections where connection_ws_id = {$data['userId']}")['count'] > 0;
        if(!$isConnection){
            $connection_ws_id = intval($data['userId']);
            $connection_username = $data['author'];
            $sql = "insert connections(connection_ws_id, connection_username) values($connection_ws_id, '$connection_username')";
            return $this->db->exec($sql);
        }
        else{
            return 2;
        }
    }
}