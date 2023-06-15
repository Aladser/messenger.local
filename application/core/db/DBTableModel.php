<?php

namespace core\db;
use \PDO;

// Класс модели таблицы БД
class DBTableModel{
    protected $db;

    function __construct(DBQueryCtl $db){
        $this->db = $db;
    }
}