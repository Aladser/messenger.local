<?php

namespace Aladser\Core\DB;

use Aladser\Models\ConnectionsDBTableModel;
use Aladser\Models\ContactsDBTableModel;
use Aladser\Models\MessageDBTableModel;
use Aladser\Models\UsersDBTableModel;

/** Класс модели таблицы БД */
class DBCtl
{
    private DBQueryClass $dbQueryCtl;

    public function __construct($dbAddr, $dbName, $dbUser, $dbPassword)
    {
        $this->dbQueryCtl = new DBQueryClass($dbAddr, $dbName, $dbUser, $dbPassword);
    }

    /** Возвращает таблицу пользователей
     * @return UsersDBTableModel
     */
    public function getUsers(): UsersDBTableModel
    {
        return new UsersDBTableModel($this->dbQueryCtl);
    }

    /** Возвращает таблицу контактов
     * @return ContactsDBTableModel
     */
    public function getContacts(): ContactsDBTableModel
    {
        return new ContactsDBTableModel($this->dbQueryCtl);
    }

    /** Возвращает таблицу соединений
     * @return ConnectionsDBTableModel
     */
    public function getConnections(): ConnectionsDBTableModel
    {
        return new ConnectionsDBTableModel($this->dbQueryCtl);
    }

    /** Возвращает таблицу сообщений
     * @return MessageDBTableModel
     */
    public function getMessageDBTable(): MessageDBTableModel
    {
        return new MessageDBTableModel($this->dbQueryCtl);
    }
}
