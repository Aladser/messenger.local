<?php
require_once(dirname(__DIR__, 1).'/core/DBQueryClass.php');
echo 'TableDBModel<br>';

// Класс модели таблицы БД
class TableDBModel{
    protected $db;

    function __construct(DB $db){
        $this->db = $db;
    }
}