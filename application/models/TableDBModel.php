<?php

require_once(dirname(__DIR__, 1).'/core//DBQueryClass.php');

// Класс модели таблицы БД
class TableDBModel{
    protected $db;

    function __construct(DBQueryClass $db){
        $this->db = $db;
    }
}