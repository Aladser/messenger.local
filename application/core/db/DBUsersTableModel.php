<?php

namespace core\db;
use \PDO;

class DBUsersTableModel extends DBTableModel{

    // проверить существование пользователя
    function existsUser($email){
        $query = $this->db->query("select count(*) as count from users where user_email = '$email'");
        $count = $query->fetch(PDO::FETCH_ASSOC)['count'];
        return intval($count) === 1;
    }

    // проверка авторизации
    function isAuthentication($email, $password){
        $query = $this->db->query("select user_password from users where user_email='$email'");
        $passhash = $query->fetch(PDO::FETCH_ASSOC)['user_password'];
        //return $password === $passhash;
        return password_verify($password, $passhash);
    }
    
    // добавить нового пользователя
    function addUser($email, $password){
        $password = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->exec("insert into users(user_email, user_password) values('$email', '$password')");
    }

    // добавить хэш пользователю
    function addUserHash($email, $hash){
        return $this->db->exec("UPDATE users SET user_hash='$hash' WHERE user_email='$email'");
    }

    // проверить хэш пользователя
    function checkUserHash($email, $hash){
        $query = $this->db->query("select count(*) as count from users where user_email = '$email' and user_hash='$hash'");
        $hash = $query->fetch(PDO::FETCH_ASSOC)['count'];
        return intval($hash) === 1;
    }

    // подтвердить почту
    function confirmEmail($email){
        $this->db->query("UPDATE users SET user_hash=NULL WHERE user_email='$email'");
        return $this->db->exec("UPDATE users SET user_email_confirmed=1 WHERE user_email='$email'");
    }

}