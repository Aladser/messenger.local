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
        $passHash = $this->get($email, 'user_password');

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

    /** Cписок пользователей по шаблону почты или никнейма.
     *
     * @param string $phrase фраза
     */
    public function getUsersByPhrase(string $phrase, string $notEmail): array
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
        $args = ['email' => $notEmail, 'phrase' => $phrase];

        return $this->dbQuery->queryPrepared($sql, $args, false);
    }

    // обновить пользовательские данные
    public function setUserData($data): bool
    {
        $email = $data['user_email'];
        $condition = [
            'condition_field_name' => 'user_email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $data, $condition);

        return $isUpdated;
    }
}
