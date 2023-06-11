<?php

require_once('TableDBModel.php');

class UsersDBModel extends TableDBModel{

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
    function addUserHash($email){
        $hash = self::generateCode();
        $this->db->query("UPDATE users SET user_hash='$hash' WHERE user_email='$email'");
    }

    // получить хэш пользователя
    function getUserHash($email){
        $query = $this->db->query("select user_hash from users where user_email = '$email'");
        $hash = $query->fetch(PDO::FETCH_ASSOC)['user_hash'];
        return $hash;
    }

    // проверить хэш пользователя
    function checkUserHash($login, $hash){
        $query = $this->db->query("select count(*) as count from users where user_email = '$login' and user_hash='$hash'");
        $hash = $query->fetch(PDO::FETCH_ASSOC)['count'];
        return intval($hash) === 1;
    }

    // генерация случайной строки
    static function generateCode($length=6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];
        }
        return $code;
    }

}