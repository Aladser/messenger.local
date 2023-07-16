<?php

namespace core\db;

/**
 *  класс БД таблицы контактов пользователей 
 *  отвечает за запросы в таблице контактов пользователей
*/
class ContactsDBTableModel extends DBTableModel{
    // добавить контакт
    function addContact($contactId, $userId){
        $isContact = $this->db->query("select * from contacts where (cnt_user_id = $userId and cnt_contact_id = $contactId)");
        return $isContact ? 1 :  $this->db->exec("insert into contacts(cnt_user_id, cnt_contact_id) values($userId, $contactId)");
    }

    function getContacts($userId){
        $sql = "
        select user_nickname as username, user_photo from users 
        where user_hide_email = 1 
        and user_id in (select cnt_contact_id from contacts where cnt_user_id=$userId)
        union 
        select user_email as username, user_photo from users 
        where user_hide_email = 0
        and user_id in (select cnt_contact_id from contacts where cnt_user_id=$userId)
        ";
        return $this->db->query($sql, false);
    }
}