<?php

namespace App\Core;

use function App\config;

class Model
{
    protected DBQuery $dbQuery;

    public function __construct()
    {
        $this->dbQuery = new DBQuery(config('HOST_DB'), config('NAME_DB'), config('USER_DB'), config('PASS_DB'));
    }
}
