<?php

namespace core;

class ConfigClass{
	// подключение к БД
	private const HOST_DB = 'localhost';
	private const NAME_DB = 'messenger';
	private const USER_DB = 'admin';
	private const PASS_DB = '@admin@';
	private $DBQueryCtl; // класс запросов к БД
	// настроцки почтового сервера
	private const SMTP_SRV = 'smtp.mail.ru';
	private const EMAIL_USERNAME = 'aladser@mail.ru';
	private const EMAIL_PASSWORD = 'BEt7tei0Nc2YhK4s1jix';
	private const SMTP_SECURE = 'ssl';
	private const SMTP_PORT = 465;
	private const EMAIL_SENDER = 'aladser@mail.ru';
	private const EMAIL_SENDER_NAME = 'Messenger Admin';
	private $EMailSender; // класс отправки писем
	private $users; // пользователи
	private $contacts; // контакты пользователя
	private $connections; // соединения
	private $messageDBTable; // БД таблица сообщений
	// демон вебсокета сообщений
	public const CHAT_WS_PORT = 8888;
	public const SITE_ADDR = '127.0.0.1';

	function __construct(){
		$this->DBQueryCtl = new \core\db\DBQueryCtl(
			self::HOST_DB, 
			self::NAME_DB, 
			self::USER_DB, 
			self::PASS_DB
		);
		$this->EMailSender = new \core\phpmailer\EMailSender(
			self::SMTP_SRV, 
			self::EMAIL_USERNAME, 
			self::EMAIL_PASSWORD, 
			self::SMTP_SECURE, 
			self::SMTP_PORT, 
			self::EMAIL_SENDER, 
			self::EMAIL_SENDER_NAME
		);
		
		$this->users = new \core\db\UsersDBTableModel($this->DBQueryCtl);
		$this->contacts = new \core\db\ContactsDBTableModel($this->DBQueryCtl);
		$this->connections = new \core\db\ConnectionsDBTableModel($this->DBQueryCtl);
		$this->messageDBTable = new \core\db\MessageDBTableModel($this->DBQueryCtl);
	}

	function getDBQueryCtl(){return $this->DBQueryCtl;}
	function getEmailSender(){return $this->EMailSender;}
	function getUsers(){return $this->users;}
	function getContacts(){return $this->contacts;}
	function getConnections(){return $this->connections;}
	function getMessageDBTable(){return $this->messageDBTable;}
}                                                                                                                                                                                                                                                      