<?php

namespace Aladser\Core\DB;

/** Класс модели таблицы БД */
class DBTableModel
{
    protected $db;

    public function __construct(DBQueryClass $db)
    {
        $this->db = $db;
    }
}
