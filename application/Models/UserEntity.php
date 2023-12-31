<?php

namespace App\Models;

use App\Core\Model;

/** класс БД таблицы пользователей */
class UserEntity extends Model
{
    // Проверка существования значения
    public function exists(string $field, mixed $value)
    {
        $sql = "select count(*) as count from users where $field = :value";
        $args = ['value' => $value];

        return $this->dbQuery->queryPrepared($sql, $args)['count'] > 0;
    }

    // Поле строки таблицы
    public function get(string $email, string $field): mixed
    {
        $sql = "select $field from users where user_email = :email";
        $args = ['email' => $email];

        return $this->dbQuery->queryPrepared($sql, $args)[$field];
    }

    // Получить ID пользователя
    public function getIdByName(string $publicUsername)
    {
        $sql = 'select user_id from users 
                where user_email = :publicUsername or user_nickname=:publicUsername';
        $args = ['publicUsername' => $publicUsername];
        $id = $this->dbQuery->queryPrepared($sql, $args)['user_id'];

        return $id;
    }

    // Получить публичное имя пользователя
    public function getPublicUsername(int $userId)
    {
        $sql = '
            select getPublicUserName(user_email, user_nickname, user_hide_email) as username 
            from users where user_id = :userId';
        $args = ['userId' => $userId];
        $username = $this->dbQuery->queryPrepared($sql, $args)['username'];

        return $username;
    }

    // Проверка авторизации
    public function verify(string $email, string $password): bool
    {
        $sql = 'select user_password from users where user_email=:email';
        $args = ['email' => $email];
        $passHash = $this->dbQuery->queryPrepared($sql, $args)['user_password'];

        return password_verify($password, $passHash) == 1;
    }

    // Добавить нового пользователя
    public function add($email, $password): bool
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $fields = ['user_email' => $email, 'user_password' => $password];
        $userId = $this->dbQuery->insert('users', $fields);

        return $userId;
    }

    // Добавить хэш пользователю
    public function addUserHash($email, $hash): bool
    {
        $fieldArray = ['user_hash' => $hash];
        $condition = [
            'condition_field_name' => 'user_email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $fieldArray, $condition);

        return $isUpdated;
    }

    /** Подтвердить почту */
    public function confirmEmail($email)
    {
        $fieldArray = ['user_email_confirmed' => 1, 'user_hash' => null];
        $condition = [
            'condition_field_name' => 'user_email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $fieldArray, $condition);

        return $isUpdated;
    }

    // Проверить хэш пользователя
    public function isUserHash($email, $hash): bool
    {
        $sql = 'select count(*) as count from users where user_email = :email and user_hash = :hash';
        $args = ['email' => $email, 'hash' => $hash];

        return $this->dbQuery->queryPrepared($sql, $args)['count'] === 1;
    }

    // список пользователей по шаблону почты или никнейма
    public function getUsers($phrase, $email)
    {
        $phrase = "%$phrase%";
        // список пользователей, подходящие по шаблону
        $sql = '
            select user_id as user, user_nickname as name, user_photo as photo 
            from users 
            where user_hide_email = 1 and user_email != :email and user_nickname like :phrase
            union 
            select user_id as user, user_email as name, user_photo as photo 
            from users 
            where user_hide_email = 0 and user_email != :email and user_email like :phrase;
        ';
        $args = ['email' => $email, 'phrase' => $phrase];

        return $this->dbQuery->queryPrepared($sql, $args, false);
    }

    // изменить пользовательские данные в Бд
    public function setUserData($data): bool
    {
        $rslt = false;
        $email = $data['user_email'];

        // запись никнейма
        $nickname = $data['user_nickname'];
        $rslt |= $this->isEqualData($nickname, 'user_nickname', $email) ?
            true :
            $this->dbQuery->exec("update users set user_nickname = '$nickname' where user_email='$email'");

        // запись скрытия почты
        $hideEmail = $data['user_hide_email'];
        $rslt |= $this->isEqualData($hideEmail, 'user_hide_email', $email) ?
            true :
            $this->dbQuery->exec("update users set user_hide_email = '$hideEmail' where user_email='$email'");

        // запись фото
        $photo = $data['user_photo'];
        $rslt |= $this->isEqualData($photo, 'user_photo', $email) ?
            true :
            $this->dbQuery->exec("update users set user_photo = '$photo' where user_email='$email'");

        return $rslt;
    }

    // сравнение новых данных и в БД
    private function isEqualData($data, $field, $email): bool
    {
        $sql = "select $field from users WHERE user_email=:email";
        $args = ['email' => $email];
        $dbData = $this->dbQuery->queryPrepared($sql, $args)[$field];

        return $data === $dbData;
    }
}
