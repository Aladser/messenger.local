<?php

namespace Aladser\Core;

use Aladser\Models\ConnectionEntity;
use Aladser\Models\ContactEntity;
use Aladser\Models\MessageEntity;
use Aladser\Models\UserEntity;

/** Класс модели таблицы БД */
class DBCtl
{
    private DBQuery $dbQueryCtl;

    public function __construct($dbAddr, $dbName, $dbUser, $dbPassword)
    {
        $this->dbQueryCtl = new DBQuery($dbAddr, $dbName, $dbUser, $dbPassword);
    }

    /** Возвращает таблицу пользователей. */
    public function getUsers(): UserEntity
    {
        return new UserEntity($this->dbQueryCtl);
    }

    /** Возвращает таблицу контактов.*/
    public function getContacts(): ContactEntity
    {
        return new ContactEntity($this->dbQueryCtl);
    }

    /** Возвращает таблицу соединений.*/
    public function getConnections(): ConnectionEntity
    {
        return new ConnectionEntity($this->dbQueryCtl);
    }

    /** Возвращает таблицу сообщений.*/
    public function getMessageDBTable(): MessageEntity
    {
        return new MessageEntity($this->dbQueryCtl);
    }
}
