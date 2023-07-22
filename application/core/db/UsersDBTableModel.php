<?php

namespace core\db;

/**
 *  класс БД таблицы пользователей 
 *  отвечает за все запросы в таблице пользователей
*/
class UsersDBTableModel extends DBTableModel{

    // проверить существование пользователя
    public function existsUser($email){
        return $this->db->query("select count(*) as count from users where user_email = '$email'")['count'] == 1;
    }

    // проверка авторизации
    public function checkUser($email, $password){
        $passhash = $this->db->query("select user_password from users where user_email='$email'")['user_password'];
        return password_verify($password, $passhash);
    }
    
    // добавить нового пользователя
    public function addUser($email, $password){
        $password = password_hash($password, PASSWORD_DEFAULT);
        return $this->db->exec("insert into users(user_email, user_password) values('$email', '$password')");
    }

    // добавить хэш пользователю
    public function addUserHash($email, $hash){
        return $this->db->exec("UPDATE users SET user_hash='$hash' WHERE user_email='$email'");
    }
    // проверить хэш пользователя
    public function checkUserHash($email, $hash){
        $hash = $this->db->query("select count(*) as count from users where user_email = '$email' and user_hash='$hash'")['count'];
        return $hash == 1;
    }

    // подтвердить почту
    public function confirmEmail($email){
        return $this->db->exec("UPDATE users SET user_email_confirmed=1 WHERE user_email='$email'");
    }

    // проверить уникальность никнейма
    public function isUniqueNickname($nickname){
        return $this->db->query("select count(*) as count from users where user_nickname='$nickname'")['count'] == 0;
    }

    /** получить публичное имя пользователя из ID */
    public function getPublicUsername(int $userId){
        return $this->db->query("select getPublicUserName(user_email, user_nickname, user_hide_email) as username from users where user_id = $userId")['username'];
    }

    // получить публичное имя пользователя из почты
    public function getPublicUsernameFromEmail(string $userEmail){
        return $this->db->query("select getPublicUserName(user_email, user_nickname, user_hide_email) as username from users where user_email = '$userEmail'")['username'];
    }

    // получить ID пользователя
    public function getUserId(string $publicUserName){
        return $this->db->query("select user_id from users where user_email = '$publicUserName' or user_nickname='$publicUserName'")['user_id'];
    }

    // список пользователей по шаблону почты или никнейма
    public function getUsers($phrase, $email){
        // список пользователей, подходящие по шаблону
        $sql = "
        select user_id, user_nickname as username, user_photo from users where user_nickname  != '' and user_nickname is not null and user_email != '$email' and user_nickname  like '%$phrase%'
        and user_email not in (select * from unhidden_emails where user_email  like '%$phrase%')
        union 
        select user_id, user_email, user_photo as username from users where user_hide_email  = 0 and user_email != '$email' and user_email like '%$phrase%';
        ";
        return $this->db->query($sql, false);
    }

    // получить пользовательские данные
    public function getUserData($email){
        $dbData = $this->db->query("select user_nickname, user_hide_email, user_photo from users where user_email = '$email'", false);
        $data['user-email'] = $email;
        $data['user_nickname'] = $dbData[0]['user_nickname'];
        $data['user_hide_email'] = $dbData[0]['user_hide_email'];
        $data['user_photo'] = $dbData[0]['user_photo'];
        return $data;
    }

    // изменить пользовательские данные в Бд
    public function setUserData($data){
        $rslt = false; 
        $email = $data['user_email']; 

        // запись никнейма
        $nickname = $data['user_nickname'];
        $rslt |= $this->isEqualData($nickname, 'user_nickname', $email) ? true : $this->db->exec("update users set user_nickname = '$nickname' where user_email='$email'");

        // запись скрытия почты
        $hideEmail = $data['user_hide_email'];
        $rslt |= $this->isEqualData($hideEmail, 'user_hide_email', $email) ? true : $this->db->exec("update users set user_hide_email = '$hideEmail' where user_email='$email'");

        // запись фото
        $photo = $data['user_photo'];
        $rslt |= $this->isEqualData($photo, 'user_photo', $email) ? true : $this->db->exec("update users set user_photo = '$photo' where user_email='$email'");

        return $rslt;
    }

    // сравнение новых данных и в БД
    private function isEqualData($data, $field, $email){
        $dbData = $this->db->query("select $field from users WHERE user_email='$email'")[$field];
        return $data === $dbData;
    }
}