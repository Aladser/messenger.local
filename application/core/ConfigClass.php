<?php
namespace core;

use core\db\DBQueryCtl;
use \core\db\UsersDBTableModel;
use \core\db\ContactsDBTableModel;
use \core\db\ConnectionsDBTableModel;
use \core\db\MessageDBTableModel;

class ConfigClass
{
	// подключение к БД
	private const HOST_DB = 'localhost';
	private const NAME_DB = 'messenger';
	private const USER_DB = 'admin';
	private const PASS_DB = '@admin@';

	// настроцки почтового сервера
	private const SMTP_SRV = 'smtp.mail.ru';
	private const EMAIL_USERNAME = 'aladser@mail.ru';
	private const EMAIL_PASSWORD = 'BEt7tei0Nc2YhK4s1jix';
	private const SMTP_SECURE = 'ssl';
	private const SMTP_PORT = 465;
	private const EMAIL_SENDER = 'aladser@mail.ru';
	private const EMAIL_SENDER_NAME = 'Messenger Admin';

	private $DBQueryCtl; // класс запросов к БД
	private $EMailSender; // класс отправки писем
	private $users; // пользователи
	private $contacts; // контакты пользователя
	private $connections; // соединения
	private $messageDBTable; // БД таблица сообщений

	// демон вебсокета сообщений
	public const CHAT_WS_PORT = 8888;
	public const SITE_ADDR = '127.0.0.1';

	public function __construct()
	{
		$this->DBQueryCtl = new DBQueryCtl
		(
			self::HOST_DB, 
			self::NAME_DB, 
			self::USER_DB, 
			self::PASS_DB
		);
		$this->EMailSender = new EMailSender
		(
			self::SMTP_SRV, 
			self::EMAIL_USERNAME, 
			self::EMAIL_PASSWORD, 
			self::SMTP_SECURE, 
			self::SMTP_PORT, 
			self::EMAIL_SENDER, 
			self::EMAIL_SENDER_NAME
		);
		
		$this->users = new UsersDBTableModel($this->DBQueryCtl);
		$this->contacts = new ContactsDBTableModel($this->DBQueryCtl);
		$this->connections = new ConnectionsDBTableModel($this->DBQueryCtl);
		$this->messageDBTable = new MessageDBTableModel($this->DBQueryCtl);
	}

	public function getDBQueryCtl()
	{
		return $this->DBQueryCtl;
	}

	public function getEmailSender()
	{
		return $this->EMailSender;
	}

	public function getUsers()
	{
		return $this->users;
	}

	public function getContacts()
	{
		return $this->contacts;
	}

	public function getConnections()
	{
		return $this->connections;
	}
	
	public function getMessageDBTable()
	{
		return $this->messageDBTable;
	}
}
                                                                                                                                                                                                                                                      