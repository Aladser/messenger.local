<?php

namespace Aladser\Models;

use Aladser\Core\DB\DBQueryClass;

/** Класс модели таблицы БД */
class DBTableModel
{
    protected DBQueryClass $db;

    public function __construct(DBQueryClass $db)
    {
        $this->db = $db;
    }

    // проверка существования значения
    public function exists($table, $field, $value, $valueType = 'string')
    {
        $sql = "select count(*) as count from $table where $field = :value";

        return $this->db->queryPrepared($sql, ['value' => $value])['count'] > 0;
    }
}
