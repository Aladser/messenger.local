<?php
namespace core\db;

/** Класс модели таблицы БД */
class DBTableModel
{
    protected $db;

    function __construct(DBQueryCtl $db)
    {
        $this->db = $db;
    }
}
