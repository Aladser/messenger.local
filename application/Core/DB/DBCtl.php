<?php

namespace Aladser\Core\DB;

use Aladser\Core\DB\DBQueryClass;
use Aladser\Core\DB\ConnectionsDBTableModel;
use Aladser\Core\DB\ContactsDBTableModel;
use Aladser\Core\DB\MessageDBTableModel;
use Aladser\Core\DB\UsersDBTableModel;

/** Класс модели таблицы БД */
class DBCtl
{
    private $dbQueryCtl;
    private $users;
    private $contacts;
    private $connections;
    private $messages;

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
