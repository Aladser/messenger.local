<?php

namespace Aladser\Core;

use Aladser\Core\DB\DBQueryClass;

class Model
{
    protected DBQueryClass $db;

    public function __construct(DBQueryClass $db)
    {
        $this->db = $db;
    }
}
