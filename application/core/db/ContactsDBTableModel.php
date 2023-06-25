<?php

namespace core\db;

/**
 *  класс БД таблицы контактов пользователей 
 *  отвечает за все запросы в таблице контактов пользователей
*/
class ContactsDBTableModel extends DBTableModel{
    // добавить контакт
    function addContact($contact, $email){
        $user_id = $this->db->query("select user_id from users where user_email = '$email'")['user_id']; // кому добавлятиь контакт

        // id контакта. Учитывается, что в nickname может быть @
        $contact_id = $this->db->query("select user_id from users where user_email = '$contact'")['user_id'];
        if($contact_id == ''){
            $contact_id = $this->db->query("select user_id from users where user_nickname = '$contact'")['user_id'];
        }
 
        // добавление контакта
        $isContact = $this->db->query("select id from contacts where user_id = $user_id and contact_id = $contact_id") != '';
        return $isContact ? 1 :  $this->db->exec("insert into contacts(user_id, contact_id) values('$user_id','$contact_id')");
    }

    function getContacts($email){
        $sql = "
        select user_nickname as username, user_photo from users 
        where user_nickname!='' 
        and user_nickname is not null 
        and user_email!='$email'
        and user_email not in (select * from unhidden_emails)
        and user_id in (select contact_id from contacts where user_id=(select user_id from users where user_email='$email'))
        union 
        select user_email as username, user_photo from users 
        where user_hide_email=0 
        and user_email!='$email'
        and user_id in (select contact_id from contacts where user_id=(select user_id from users where user_email='$email'))
        ";
        return $this->db->query($sql, false);
    }
}