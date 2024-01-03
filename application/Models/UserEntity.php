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
        $sql = "select $field from users where email = :email";
        $args = ['email' => $email];

        return $this->dbQuery->queryPrepared($sql, $args)[$field];
    }

    // Получить ID пользователя
    public function getIdByName(string $publicUsername)
    {
        $sql = 'select id from users 
                where email = :publicUsername or nickname=:publicUsername';
        $args = ['publicUsername' => $publicUsername];
        $id = $this->dbQuery->queryPrepared($sql, $args)['id'];

        return $id;
    }

    // Получить публичное имя пользователя
    public function getPublicUsername(int $userId)
    {
        $sql = '
            select getPublicUserName(email, nickname, hide_email) as username 
            from users where id = :userId';
        $args = ['userId' => $userId];
        $username = $this->dbQuery->queryPrepared($sql, $args)['username'];

        return $username;
    }

    // Проверка авторизации
    public function verify(string $email, string $password): bool
    {
        $passHash = $this->get($email, 'password');

        return password_verify($password, $passHash) == 1;
    }

    // Добавить нового пользователя
    public function add($email, $password): bool
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $fields = ['email' => $email, 'password' => $password];
        $userId = $this->dbQuery->insert('users', $fields);

        return $userId;
    }

    // Добавить хэш пользователю
    public function addUserHash($email, $hash): bool
    {
        $fieldArray = ['hash' => $hash];
        $condition = [
            'condition_field_name' => 'email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $fieldArray, $condition);

        return $isUpdated;
    }

    /** Подтвердить почту */
    public function confirmEmail($email)
    {
        $fieldArray = ['email_confirmed' => 1, 'hash' => null];
        $condition = [
            'condition_field_name' => 'email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $fieldArray, $condition);

        return $isUpdated;
    }

    // Проверить хэш пользователя
    public function isUserHash($email, $hash): bool
    {
        $sql = 'select count(*) as count from users where email = :email and hash = :hash';
        $args = ['email' => $email, 'hash' => $hash];
        $isHashCorrected = $this->dbQuery->queryPrepared($sql, $args)['count'] === 1;

        return $isHashCorrected;
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
            select id as user, nickname as name, photo 
            from users 
            where hide_email = 1 and email != :email and nickname like :phrase
            union 
            select id as user, email as name, photo 
            from users 
            where hide_email = 0 and email != :email and email like :phrase;
        ';
        $args = ['email' => $notEmail, 'phrase' => $phrase];
        $userList = $this->dbQuery->queryPrepared($sql, $args, false);

        return $userList;
    }

    // обновить пользовательские данные
    public function setUserData($data): bool
    {
        $email = $data['email'];
        $condition = [
            'condition_field_name' => 'email',
            'condition_sign' => '=',
            'condition_field_value' => $email,
        ];
        $isUpdated = $this->dbQuery->update('users', $data, $condition);

        return $isUpdated;
    }
}
