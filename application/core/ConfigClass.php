<?php

require_once('db/DB.php');

class ConfigClass{
	private const HOST_DB = 'localhost';
	private const NAME_DB = 'messenger';
	private const USER_DB = 'admin';
	private const PASS_DB = '@admin@';
	private $DB;

	function __construct(){
		$this->DB = new DB(self::HOST_DB, self::NAME_DB, self::USER_DB, self::PASS_DB);
	}

	function getDB(){
		return $DB;
	}
}

$CONFIG = new ConfigClass(); 