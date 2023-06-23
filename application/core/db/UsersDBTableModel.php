<?php

namespace core\db;

/** класс БД таблицы пользователей */
class UsersDBTableModel extends DBTableModel{

    // проверить существование пользователя
    function existsUser($email){
        $query = $this->db->query("select count(*) as count from users where user_email = '$email'");
        $count = $query->fetch(\PDO::FETCH_ASSOC)['count'];
        return intval($count) === 1;
    }
    
    // добавить нового пользователя
    function addUser($email, $password){
        $password = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->exec("insert into users(user_email, user_password) values('$email', '$password')");
    }

    // проверка авторизации
    function checkUser($email, $password){
        $query = $this->db->query("select user_password from users where user_email='$email'");
        $passhash = $query->fetch(\PDO::FETCH_ASSOC)['user_password'];
        return password_verify($password, $passhash);
    }

    // добавить хэш пользователю
    function addUserHash($email, $hash){
        return $this->db->exec("UPDATE users SET user_hash='$hash' WHERE user_email='$email'");
    }

    // проверить хэш пользователя
    function checkUserHash($email, $hash){
        $query = $this->db->query("select count(*) as count from users where user_email = '$email' and user_hash='$hash'");
        $hash = $query->fetch(\PDO::FETCH_ASSOC)['count'];
        return intval($hash) === 1;
    }

    // подтвердить почту
    function confirmEmail($email){
        $this->db->query("UPDATE users SET user_hash=NULL WHERE user_email='$email'");
        return $this->db->exec("UPDATE users SET user_email_confirmed=1 WHERE user_email='$email'");
    }

    // получить пользовательские данные
    function getUsersData($email){
        $query = $this->db->query("select user_nickname, user_hide_email, user_photo from users where user_email = '$email'");
        $dbData = $query->fetchAll();
        $data['user-email'] = $email;
        $data['user_nickname'] = $dbData[0]['user_nickname'];
        $data['user_hide_email'] = $dbData[0]['user_hide_email'];
        $data['user_photo'] = $dbData[0]['user_photo'];
        return $data;
    }


    // сравнение новых данных и в БД
    function isEqualData($data, $field, $email){
        $query = $this->db->query("select $field from users WHERE user_email='$email'");
        $dbData = $query->fetch(\PDO::FETCH_ASSOC)[$field];
        return $data === $dbData;
    }

    // изменить пользовательские данные в Бд
    function setUserData($data){
        $rslt = false; 
        $email = $data['user_email']; 

        // проверка уникальности никнейма
        

        // запись никнейма
        $nickname = $data['user_nickname'];
        $rslt = $this->isEqualData($nickname, 'user_nickname', $email) ? true : $this->db->exec("update users set user_nickname = '$nickname' where user_email='$email'");

        // запись скрытия почты
        $hideEmail = $data['user_hide_email'];
        $rslt = $this->isEqualData($hideEmail, 'user_hide_email', $email) ? true : $this->db->exec("update users set user_hide_email = '$hideEmail' where user_email='$email'");

        // запись фото
        $photo = $data['user_photo'];
        $rslt = $this->isEqualData($photo, 'user_photo', $email) ? true : $this->db->exec("update users set user_photo = '$photo' where user_email='$email'");

        return $rslt;
    }

    // проверить уникальность никнейма
    function isUniqueNickname($nickname){
        $query = $this->db->query("select count(*) as count from users where user_nickname='$nickname'");
        return $query->fetch(\PDO::FETCH_ASSOC)['count'] == 0;
    }

}