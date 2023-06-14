<?php

// Класс модели таблицы БД
class TableDBModel{
    protected $db;

    function __construct(DBQueryClass $db){
        $this->db = $db;
    }
}