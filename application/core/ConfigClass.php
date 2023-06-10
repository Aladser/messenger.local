<?php

require_once('DBQueryClass.php');

class ConfigClass{
	private const HOST_DB = 'localhost';
	private const NAME_DB = 'messenger';
	private const USER_DB = 'admin';
	private const PASS_DB = '@admin@';
	private $DBQueryClass;

	function __construct(){
		$this->DBQueryClass = new DBQueryClass(self::HOST_DB, self::NAME_DB, self::USER_DB, self::PASS_DB);
	}

	function getDBQueryClass(){
		return $this->DBQueryClass;
	}
}

$CONFIG = new ConfigClass(); 