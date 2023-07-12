<?php

namespace core\db;

/**
 *  класс БД таблицы контактов пользователей 
 *  отвечает за запросы в таблице контактов пользователей
*/
class ContactsDBTableModel extends DBTableModel{
    // добавить контакт
    function addContact($contact, $email){
        // id пользователя-хоста
        $user_id = $this->db->query("select user_id from users where user_email = '$email'")['user_id']; 
        // id контакта
        $contact_id = $this->db->query("select user_id from users where user_email = '$contact' or user_nickname = '$contact'")['user_id'];
        // добавление контакта
        $isContact = $this->db->query("select * from contacts where (cnt_user_id = $user_id and cnt_contact_id = $contact_id)");
        return $isContact ? 1 :  $this->db->exec("insert into contacts(cnt_user_id, cnt_contact_id) values('$user_id','$contact_id')");
    }

    function getContacts($email){
        $sql = "
        select user_nickname as username, user_photo from users 
        where user_hide_email = 1 
        and user_id in (select cnt_contact_id from contacts where cnt_user_id=(select user_id from users where user_email='$email'))
        union 
        select user_email as username, user_photo from users 
        where user_hide_email = 0
        and user_id in (select cnt_contact_id from contacts where cnt_user_id=(select user_id from users where user_email='$email'))
        ";
        return $this->db->query($sql, false);
    }
}