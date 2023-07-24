<?php

namespace Aladser\core\db;

/** Класс модели таблицы БД */
class DBTableModel
{
    protected $db;

    public function __construct(DBQueryCtl $db)
    {
        $this->db = $db;
    }
}
