<?php

namespace Aladser\Core;

class Model
{
    protected DBQuery $db;

    public function __construct(DBQuery $db)
    {
        $this->db = $db;
    }
}
