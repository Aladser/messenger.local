<?php

namespace Aladser\Core;

use Aladser\Core\DB\ConnectionsDBTableModel;
use Aladser\Core\DB\ContactsDBTableModel;
use Aladser\Core\DB\DBQueryCtl;
use Aladser\Core\DB\MessageDBTableModel;
use Aladser\Core\DB\UsersDBTableModel;
use Aladser\Core\ScriptLinuxProcess;

class ConfigClass
{
    // подключение к БД
    private const HOST_DB = 'localhost';
    private const NAME_DB = 'messenger';
    private const USER_DB = 'admin';
    private const PASS_DB = '@admin@';

    // настройки почтового сервера
    private const SMTP_SRV = 'smtp.mail.ru';
    private const EMAIL_USERNAME = 'aladser@mail.ru';
    private const EMAIL_PASSWORD = 'BEt7tei0Nc2YhK4s1jix';
    private const SMTP_SECURE = 'ssl';
    private const SMTP_PORT = 465;
    private const EMAIL_SENDER = 'aladser@mail.ru';
    private const EMAIL_SENDER_NAME = 'Messenger Admin';

    // класс запросов к БД
    private $dbQueryCtl;

    // демон вебсокета сообщений
    private $websocketProcessName;
    private $websocketProcessFile;
    private $websocketProcessLogFile;
    private $pidsListFile;
    public const CHAT_WS_PORT = 8888;
    public const SITE_ADDR = '127.0.0.1';

    public function __construct()
    {
        $this->dbQueryCtl = new DBQueryCtl(
            self::HOST_DB,
            self::NAME_DB,
            self::USER_DB,
            self::PASS_DB
        );

        $this->websocketProcessName = 'chat-server';
        $this->websocketProcessFile = dirname(__DIR__, 1) . '/chat-server.php';
        $this->websocketProcessLogFile = dirname(__DIR__, 2) . '/logs/websocket.log';
        $this->pidsListFile = dirname(__DIR__, 2).'/logs/pids.log';
    }

    /**
     * Возвращает класс запросов БД
     * @return DBQueryCtl
     */
    public function getDBQueryCtl(): DBQueryCtl
    {
        return $this->dbQueryCtl;
    }

    /**
     * Возвращает класс Отправителя писем
     * @return EMailSender
     */
    public function getEmailSender(): EMailSender
    {
        return new EMailSender(
            self::SMTP_SRV,
            self::EMAIL_USERNAME,
            self::EMAIL_PASSWORD,
            self::SMTP_SECURE,
            self::SMTP_PORT,
            self::EMAIL_SENDER,
            self::EMAIL_SENDER_NAME
        );
    }

    /**
     * Возвращает таблицу пользователей
     * @return UsersDBTableModel
     */
    public function getUsers(): UsersDBTableModel
    {
        return new UsersDBTableModel($this->dbQueryCtl);
    }

    /**
     * Возвращает таблицу контактов
     * @return ContactsDBTableModel
     */
    public function getContacts(): ContactsDBTableModel
    {
        return new ContactsDBTableModel($this->dbQueryCtl);
    }

    /**
     * Возвращает таблицу соединений
     * @return ConnectionsDBTableModel
     */
    public function getConnections(): ConnectionsDBTableModel
    {
        return new ConnectionsDBTableModel($this->dbQueryCtl);
    }

    /**
     * Возвращает таблицу сообщений
     * @return MessageDBTableModel
     */
    public function getMessageDBTable(): MessageDBTableModel
    {
        return new MessageDBTableModel($this->dbQueryCtl);
    }

    public function getWebsocketProcess(): ScriptLinuxProcess
    {
        return new ScriptLinuxProcess(
            $this->websocketProcessName,
            $this->websocketProcessFile,
            $this->websocketProcessLogFile,
            $this->pidsListFile
        );
    }
}
