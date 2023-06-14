<?php
require_once('DBQueryClass.php');
require_once('phpmailer/EMailSender.php');

class ConfigClass{
	// подключение к БД
	private const HOST_DB = 'localhost';
	private const NAME_DB = 'messenger';
	private const USER_DB = 'admin';
	private const PASS_DB = '@admin@';
	private $DBQueryClass; // класс запросов к БД
	// настроцки почтового сервера
	private const SMTP_SRV = 'smtp.mail.ru';
	private const EMAIL_USERNAME = 'aladser@mail.ru';
	private const EMAIL_PASSWORD = 'BEt7tei0Nc2YhK4s1jix';
	private const SMTP_SECURE = 'ssl';
	private const PORT = 465;
	private const EMAIL_SENDER = 'aladser@mail.ru';
	private const EMAIL_SENDER_NAME = 'Messenger Admin';
	private $EMailSender; // класс отправки писем

	function __construct(){
		$this->DBQueryClass = new DBQueryClass(self::HOST_DB, 
			self::NAME_DB, 
			self::USER_DB, 
			self::PASS_DB
		);
		$this->EMailSender = new EMailSender(self::SMTP_SRV, 
			self::EMAIL_USERNAME, 
			self::EMAIL_PASSWORD, 
			self::SMTP_SECURE, 
			self::PORT, 
			self::EMAIL_SENDER, 
			self::EMAIL_SENDER_NAME
		);
	}

	function getDBQueryClass(){
		return $this->DBQueryClass;
	}

	function getEmailSender(){
		return $this->EMailSender;
	}
}

$CONFIG = new ConfigClass();                                                                                                                                                                                                                                                      