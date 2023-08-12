<?php

namespace Aladser\Models;

use Aladser\Core\DB\DBQueryClass;

/** Класс модели таблицы БД */
class DBTableModel
{
    protected $db;

    public function __construct(DBQueryClass $db)
    {
        $this->db = $db;
    }
}